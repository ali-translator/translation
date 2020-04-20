<?php

namespace ALI\Translation;

use ALI\Translation\Buffer\BufferCollection;
use ALI\Translation\Buffer\BufferCatcher;
use ALI\Translation\Buffer\BufferContent;
use ALI\Translation\Buffer\BufferTranslate;
use ALI\Translation\Buffer\KeyGenerators\KeyGenerator;
use ALI\Translation\Buffer\KeyGenerators\StaticKeyGenerator;
use ALI\Translation\Exceptions\TranslateNotDefinedException;
use ALI\Translation\ContentProcessors\ContentProcessorsManager;
use ALI\Translation\Translate\MissingTranslateCallbacks\CollectorMissingTranslatesCallback;
use ALI\Translation\Translate\PhraseDecorators\TranslateDecorators\HtmlEncodeTranslateDecorator;
use ALI\Translation\Translate\PhraseDecorators\TranslatePhraseDecoratorManager;
use ALI\Translation\Translate\Sources\MySqlSource;
use ALI\Translation\Translate\Sources\SourceInterface;
use ALI\Translation\Translate\Translators\DecoratedTranslator;
use ALI\Translation\Translate\Translators\TranslatorInterface;

/**
 * Class
 */
class ALIAbc
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var null|ContentProcessorsManager
     */
    protected $contentProcessorsManager;

    /**
     * @var BufferCatcher
     */
    protected $bufferCatcher;

    /**
     * @var KeyGenerator
     */
    protected $templatesKeyGenerator;

    /**
     * @var BufferTranslate
     */
    protected $bufferTranslate;

    /**
     * @var CollectorMissingTranslatesCallback
     */
    protected $collectorTranslateCallback;

    /**
     * @param TranslatorInterface                     $translator
     * @param ContentProcessorsManager|null           $contentProcessorsManager
     * @param CollectorMissingTranslatesCallback|null $collectorTranslateCallback
     * @param BufferCatcher|null                      $bufferCatcher
     * @param KeyGenerator|null                       $templatesKeyGenerator
     * @param BufferTranslate|null                    $bufferTranslate
     * @param bool                                    $htmlEncodeBufferTranslate
     */
    public function __construct(
        TranslatorInterface $translator,
        ContentProcessorsManager $contentProcessorsManager = null,
        CollectorMissingTranslatesCallback $collectorTranslateCallback = null,
        BufferCatcher $bufferCatcher = null,
        KeyGenerator $templatesKeyGenerator = null,
        BufferTranslate $bufferTranslate = null,
        $htmlEncodeBufferTranslate = true
    )
    {
        $this->translator = $translator;
        $this->collectorTranslateCallback = $collectorTranslateCallback ?: new CollectorMissingTranslatesCallback();
        $this->translator->addMissingTranslationCallback($this->collectorTranslateCallback);

        // Make additional translator with encoding for buffer translation
        if ($htmlEncodeBufferTranslate) {
            $this->bufferTranslator = new DecoratedTranslator(
                $this->translator,
                null,
                new TranslatePhraseDecoratorManager([
                    new HtmlEncodeTranslateDecorator(),
                ])
            );
        } else {
            $this->bufferTranslator = $this->translator;
        }

        $this->contentProcessorsManager = $contentProcessorsManager;
        $this->bufferCatcher = $bufferCatcher ?: new BufferCatcher();
        $this->templatesKeyGenerator = $templatesKeyGenerator ?: new StaticKeyGenerator('{', '}');
        $this->bufferTranslate = $bufferTranslate ?: new BufferTranslate();
    }

    /**
     * @param string $phrase
     * @param array $params
     * @return string
     */
    public function translate($phrase, array $params = [])
    {
        if (!$params) {
            return $this->translator->translate($phrase);
        }
        $bufferContent = $this->generateBufferContentByTemplate($phrase, $params);
        $buffer = new BufferCollection($this->templatesKeyGenerator);
        $layoutBufferContent = new BufferContent($buffer->add($bufferContent), $buffer);

        return $this->bufferTranslate->translateBuffer($layoutBufferContent, $this->translator);
    }

    /**
     * @param array $originalPhrases
     * @return Translate\PhrasePackets\TranslatePhrasePacket
     */
    public function translateAll($originalPhrases)
    {
        return $this->translator->translateAll($originalPhrases);
    }

    /**
     * @param string $original
     * @param string $translate
     * @throws Translate\Sources\Exceptions\SourceException
     */
    public function saveTranslate($original, $translate)
    {
        $currentLanguageAlias = $this->translator->getLanguageAlias();
        $this->translator->getSource()->saveTranslate($currentLanguageAlias, $original, $translate);
    }

    /**
     * @param $original
     */
    public function deleteOriginal($original)
    {
        $this->translator->getSource()->delete($original);
    }

    /**
     * @param $content
     * @param array $params
     * @return string
     */
    public function addToBuffer($content, array $params = [])
    {
        if (!$params) {
            return $this->bufferCatcher->add($content);
        }

        $bufferContent = $this->generateBufferContentByTemplate($content, $params);

        return $this->bufferCatcher->getBuffer()->add($bufferContent);
    }

    /**
     * @param $contentContext
     * @return string
     */
    public function translateBuffer($contentContext)
    {
        $buffer = $this->bufferCatcher->getBuffer();
        $bufferContent = new BufferContent($contentContext, $buffer);

        if (!$this->contentProcessorsManager) {
            return $this->bufferTranslate->translateBuffer($bufferContent, $this->bufferTranslator);
        }

        if ($this->isSourceSensitiveForRequestsCount($this->bufferTranslator->getSource())) {
            return $this->bufferTranslate->translateBuffersWithProcessorsByOneRequest($bufferContent, $this->bufferTranslator, $this->contentProcessorsManager);
        } else {
            return $this->bufferTranslate->translateBuffersWithProcessors($bufferContent, $this->bufferTranslator, $this->contentProcessorsManager);
        }
    }

    /**
     * @param SourceInterface $source
     * @return bool
     */
    protected function isSourceSensitiveForRequestsCount(SourceInterface $source)
    {
        switch (get_class($source)) {
            case MySqlSource::class:
                return true;
                break;
        }

        return false;
    }

    /**
     * Save originals without translate to source
     */
    public function saveOriginalsWithoutTranslates()
    {
        $originalsPacketWithoutTranslates = $this->collectorTranslateCallback->getOriginalPhrasePacket();
        if (!$originalsPacketWithoutTranslates->count()) {
            return;
        }
        $this->translator->getSource()->saveOriginals($originalsPacketWithoutTranslates->getAll());
    }

    /**
     * @return bool
     */
    public function isCurrentLanguageOriginal()
    {
        return $this->translator->isCurrentLanguageOriginal();
    }

    /**
     * @return string
     * @throws TranslateNotDefinedException
     */
    public function getCurrentLanguageAlias()
    {
        return $this->translator->getLanguageAlias();
    }

    /**
     * @return BufferCatcher
     */
    public function getBufferCatcher()
    {
        return $this->bufferCatcher;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return KeyGenerator
     */
    public function getTemplatesKeyGenerator()
    {
        return $this->templatesKeyGenerator;
    }

    /**
     * @return BufferTranslate
     */
    public function getBufferTranslate()
    {
        return $this->bufferTranslate;
    }

    /**
     * @return CollectorMissingTranslatesCallback
     */
    public function getCollectorTranslateCallback()
    {
        return $this->collectorTranslateCallback;
    }

    /**
     * @param $phrase
     * @param array $contentParams
     * @return BufferContent
     */
    protected function generateBufferContentByTemplate($phrase, array $contentParams = [])
    {
        $buffer = new BufferCollection($this->templatesKeyGenerator);

        foreach ($contentParams as $bufferId => $bufferValue) {
            $buffer->add(new BufferContent($bufferValue, null, false), $bufferId);
        }

        return new BufferContent($phrase, $buffer);
    }
}
