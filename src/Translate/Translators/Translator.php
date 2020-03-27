<?php

namespace ALI\Translation\Translate\Translators;

use ALI\Translation\Translate\Language\LanguageInterface;
use ALI\Translation\Translate\OriginalProcessors\OriginalProcessorInterface;
use ALI\Translation\Translate\PhrasePackets\TranslatePhrasePacket;
use ALI\Translation\Translate\Sources\Exceptions\SourceException;
use ALI\Translation\Translate\Sources\SourceInterface;
use ALI\Translation\Translate\TranslateProcessors\TranslateProcessorInterface;
use function is_callable;

/**
 * Class Translate
 * @package ALI
 */
class Translator implements TranslatorInterface
{
    /**
     * @var string
     */
    protected $languageAlias;

    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * @var callable[]
     */
    protected $missingTranslationCallbacks = [];

    /**
     * @var OriginalProcessorInterface[]
     */
    protected $originalProcessors = [];

    /**
     * @var TranslateProcessorInterface[]
     */
    protected $translateProcessors = [];

    /**
     * Translate constructor.
     * @param string $languageAlias
     * @param SourceInterface   $source
     */
    public function __construct($languageAlias, SourceInterface $source)
    {
        $this->languageAlias = $languageAlias;
        $this->source = $source;
    }

    /**
     * @return bool
     */
    public function isCurrentLanguageOriginal()
    {
        return $this->languageAlias === $this->source->getOriginalLanguageAlias();
    }

    /**
     * @return string
     */
    public function getLanguageAlias()
    {
        return $this->languageAlias;
    }

    /**
     * @return SourceInterface
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return callable[]
     */
    public function getMissingTranslationCallbacks()
    {
        return $this->missingTranslationCallbacks;
    }

    /**
     * @param callable $missingTranslationCallback
     */
    public function addMissingTranslationCallback(callable $missingTranslationCallback)
    {
        $this->missingTranslationCallbacks[] = $missingTranslationCallback;
    }

    /**
     * @return OriginalProcessorInterface[]
     */
    public function getOriginalProcessors()
    {
        return $this->originalProcessors;
    }

    /**
     * @param OriginalProcessorInterface[] $originalProcessors
     * @return $this
     */
    public function setOriginalProcessors($originalProcessors)
    {
        $this->originalProcessors = $originalProcessors;

        return $this;
    }

    /**
     * @param OriginalProcessorInterface $originalProcessor
     */
    public function addOriginalProcessor(OriginalProcessorInterface $originalProcessor)
    {
        $this->originalProcessors[] = $originalProcessor;
    }

    /**
     * @return TranslateProcessorInterface[]
     */
    public function getTranslateProcessors()
    {
        return $this->translateProcessors;
    }

    /**
     * @param TranslateProcessorInterface[] $translateProcessors
     * @return $this
     */
    public function setTranslateProcessors($translateProcessors)
    {
        $this->translateProcessors = $translateProcessors;

        return $this;
    }

    /**
     * @param TranslateProcessorInterface $translateProcessor
     */
    public function addTranslateProcessor(TranslateProcessorInterface $translateProcessor)
    {
        $this->translateProcessors[] = $translateProcessor;
    }

    /**
     * @param array $phrases
     * @return TranslatePhrasePacket
     */
    public function translateAll($phrases)
    {
        $translatePhrasePacket = new TranslatePhrasePacket();

        if ($this->isCurrentLanguageOriginal()) {
            foreach ($phrases as $phrase) {
                $translatePhrasePacket->addTranslate($phrase, null);
            }

            return $translatePhrasePacket;
        }

        $searchPhrases = [];
        foreach ($phrases as $phrase) {
            if (!$phrase) {
                continue;
            }
            $searchPhrases[$phrase] = $this->originalProcess($phrase);
        }

        $translatesFromSource = $this->getSource()->getTranslates(
            $searchPhrases,
            $this->getLanguageAlias()
        );

        foreach ($searchPhrases as $originalPhrase => $searchPhrase) {
            $translate = isset($translatesFromSource[$searchPhrase]) ? $translatesFromSource[$searchPhrase] : '';
            if (!$translate) {
                foreach ($this->getMissingTranslationCallbacks() as $missingTranslationCallbacks) {
                    if (is_callable($missingTranslationCallbacks)) {
                        $translate = call_user_func($missingTranslationCallbacks, $searchPhrase, $this) ?: '';
                        if ($translate) {
                            break;
                        }
                    }
                }
            }

            if ($translate !== null) {
                $translate = $this->translateProcess($originalPhrase, $translate);
            }

            $translatePhrasePacket->addTranslate($originalPhrase,$translate);
        }

        return $translatePhrasePacket;
    }

    /**
     * Fast translate without buffers and processors
     *
     * @param string $phrase
     * @return string|null
     */
    public function translate($phrase)
    {
        return $this->translateAll([$phrase])->getTranslate($phrase);
    }

    /**
     * @param $original
     * @param $translate
     * @param string $languageAlias
     * @throws SourceException
     */
    public function saveTranslate($original, $translate, $languageAlias = null)
    {
        $languageAlias = $languageAlias ?: $this->languageAlias;
        $this->getSource()->saveTranslate(
            $languageAlias,
            $this->originalProcess($original),
            $translate
        );
    }

    /**
     * Delete original and all translated phrases
     * @param $original
     */
    public function delete($original)
    {
        $this->getSource()->delete(
            $this->originalProcess($original)
        );
    }

    /**
     * @param $original
     * @return string
     */
    protected function originalProcess($original)
    {
        foreach ($this->getOriginalProcessors() as $originalProcessor) {
            $original = $originalProcessor->process($original);
        }

        return $original;
    }

    /**
     * @param string $original
     * @param string $translate
     * @return string
     */
    protected function translateProcess($original, $translate)
    {
        foreach ($this->getTranslateProcessors() as $translateProcessor) {
            $translate = $translateProcessor->process($original, $translate);
        }

        return $translate;
    }
}
