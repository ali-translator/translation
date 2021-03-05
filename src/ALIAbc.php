<?php

namespace ALI\Translation;

use ALI\Translation\Buffer\BufferContentCollection;
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
use ALI\Translation\Translate\PlainTranslator\DecoratedPlainTranslator;
use ALI\Translation\Translate\PlainTranslator\PlainTranslatorInterface;

/**
 * Class
 */
class ALIAbc
{
    /**
     * @var PlainTranslatorInterface
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
     * @param PlainTranslatorInterface                $plainTranslator
     * @param ContentProcessorsManager|null           $contentProcessorsManager
     * @param CollectorMissingTranslatesCallback|null $collectorTranslateCallback
     * @param BufferCatcher|null                      $bufferCatcher
     * @param KeyGenerator|null                       $templatesKeyGenerator
     * @param BufferTranslate|null                    $bufferTranslate
     * @param bool                                    $htmlEncodeBufferTranslate
     */
    public function __construct(
        PlainTranslatorInterface $plainTranslator,
        ContentProcessorsManager $contentProcessorsManager = null,
        CollectorMissingTranslatesCallback $collectorTranslateCallback = null,
        BufferCatcher $bufferCatcher = null,
        KeyGenerator $templatesKeyGenerator = null,
        BufferTranslate $bufferTranslate = null,
        $htmlEncodeBufferTranslate = true
    )
    {
        $this->translator = $plainTranslator;
        $this->collectorTranslateCallback = $collectorTranslateCallback ?: new CollectorMissingTranslatesCallback();
        $this->translator->addMissingTranslationCallback($this->collectorTranslateCallback);

        // Make additional translator with encoding for buffer translation
        if ($htmlEncodeBufferTranslate) {
            $this->bufferTranslator = new DecoratedPlainTranslator(
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
        if (!$phrase) {
            return $phrase;
        }

        if ($params) {
            $bufferContent = $this->generateBufferContentByTemplate($phrase, $params);
            $buffer = new BufferContentCollection($this->templatesKeyGenerator);
            $layoutBufferContent = new BufferContent($buffer->add($bufferContent), $buffer);
            $translate = $this->bufferTranslate->translateChildContentCollection($layoutBufferContent, $this->translator);
        } else {
            $translate = $this->translator->translate($phrase);
        }

        return $translate;
    }

    /**
     * @param string $phrase
     * @param array $params
     * @return string
     */
    public function translateWithFallback($phrase, array $params = [])
    {
        $translate = $this->translate($phrase, $params);
        if (!$translate) {
            return $phrase;
        }

        return $translate;
    }

    /**
     * @param array $originalPhrases
     * @return Translate\PhraseCollection\TranslatePhraseCollection
     */
    public function translateAll($originalPhrases)
    {
        return $this->translator->translateAll($originalPhrases);
    }

    /**
     * @param string $original
     * @param string $translate
     * @throws Translate\Source\Exceptions\SourceException
     */
    public function saveTranslate($original, $translate)
    {
        $currentLanguageAlias = $this->translator->getTranslationLanguageAlias();
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

        return $this->bufferCatcher->getBufferContentCollection()->add($bufferContent);
    }

    /**
     * @param $contentContext
     * @return string
     */
    public function translateBuffer($contentContext)
    {
        $buffer = $this->bufferCatcher->getBufferContentCollection();
        $bufferContent = new BufferContent($contentContext, $buffer);

        if (!$this->contentProcessorsManager) {
            return $this->bufferTranslate->translateChildContentCollection($bufferContent, $this->bufferTranslator);
        }

        if ($this->translator->getSource()->isSensitiveForRequestsCount()) {
            return $this->bufferTranslate->translateBuffersWithProcessorsByOneRequest($bufferContent, $this->bufferTranslator, $this->contentProcessorsManager);
        } else {
            return $this->bufferTranslate->translateBuffersWithProcessors($bufferContent, $this->bufferTranslator, $this->contentProcessorsManager);
        }
    }

    /**
     * Save originals without translate to source
     */
    public function saveMissedOriginals()
    {
        $originalsPacketWithoutTranslates = $this->collectorTranslateCallback->getOriginalPhraseCollection();
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
        return $this->translator->getTranslationLanguageAlias();
    }

    /**
     * @return BufferCatcher
     */
    public function getBufferCatcher()
    {
        return $this->bufferCatcher;
    }

    /**
     * @return PlainTranslatorInterface
     */
    public function getPlainTranslator()
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
     * @param string $phrase
     * @param array $contentParams
     * @return BufferContent
     */
    protected function generateBufferContentByTemplate($phrase, array $contentParams = [])
    {
        $bufferContentCollection = new BufferContentCollection($this->templatesKeyGenerator);

        foreach ($contentParams as $bufferId => $bufferValue) {
            $bufferContentCollection->add(new BufferContent($bufferValue, null, [BufferContent::OPTION_WITH_CONTENT_TRANSLATION => false]), $bufferId);
        }

        return new BufferContent($phrase, $bufferContentCollection);
    }
}
