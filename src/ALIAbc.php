<?php

namespace ALI\Translation;

use ALI\Translation\Buffer\Buffer;
use ALI\Translation\Buffer\BufferCaptcher;
use ALI\Translation\Buffer\BufferContent;
use ALI\Translation\Buffer\BufferTranslate;
use ALI\Translation\Buffer\KeyGenerators\KeyGenerator;
use ALI\Translation\Buffer\KeyGenerators\StaticKeyGenerator;
use ALI\Translation\Exceptions\TranslateNotDefinedException;
use ALI\Translation\Processors\ProcessorsManager;
use ALI\Translation\Translate\Language\LanguageInterface;
use ALI\Translation\Translate\MissingTranslateCallbacks\CollectorMissingTranslatesCallback;
use ALI\Translation\Translate\Sources\MySqlSource;
use ALI\Translation\Translate\Sources\SourceInterface;
use ALI\Translation\Translate\Translators\TranslatorInterface;

/**
 * Class ALI
 * @package ALI
 */
class ALIAbc
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var null|ProcessorsManager
     */
    protected $processorsManager;

    /**
     * @var BufferCaptcher
     */
    protected $bufferCaptcher;

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
     * @param TranslatorInterface $translator
     * @param ProcessorsManager|null $processorsManager
     */
    public function __construct(TranslatorInterface $translator, ProcessorsManager $processorsManager = null)
    {
        $this->translator = $translator;
        $this->collectorTranslateCallback = new CollectorMissingTranslatesCallback();
        $this->translator->addMissingTranslationCallback($this->collectorTranslateCallback);

        $this->processorsManager = $processorsManager;
        $this->bufferCaptcher = new BufferCaptcher();
        $this->templatesKeyGenerator = new StaticKeyGenerator('{', '}');
        $this->bufferTranslate = new BufferTranslate();
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
        $buffer = new Buffer($this->templatesKeyGenerator);
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
            return $this->bufferCaptcher->add($content);
        }

        $bufferContent = $this->generateBufferContentByTemplate($content, $params);

        return $this->bufferCaptcher->getBuffer()->add($bufferContent);
    }

    /**
     * @param $contentContext
     * @return string
     */
    public function translateBuffer($contentContext)
    {
        $buffer = $this->bufferCaptcher->getBuffer();
        $bufferContent = new BufferContent($contentContext, $buffer);

        if (!$this->processorsManager) {
            return $this->bufferTranslate->translateBuffer($bufferContent, $this->translator);
        }

        if ($this->isSourceSensitiveForRequestsCount($this->translator->getSource())) {
            return $this->bufferTranslate->translateBuffersWithProcessorsByOneRequest($bufferContent, $this->translator, $this->processorsManager);
        } else {
            return $this->bufferTranslate->translateBuffersWithProcessors($bufferContent, $this->translator, $this->processorsManager);
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
     * @return BufferCaptcher
     */
    public function getBufferCaptcher()
    {
        return $this->bufferCaptcher;
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return ProcessorsManager|null
     */
    public function getProcessorsManager()
    {
        return $this->processorsManager;
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
        $buffer = new Buffer($this->templatesKeyGenerator);

        foreach ($contentParams as $bufferId => $bufferValue) {
            $buffer->add(new BufferContent($bufferValue), $bufferId);
        }

        return new BufferContent($phrase, $buffer);
    }
}
