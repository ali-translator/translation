<?php

namespace ALI\Translation\Translate\Translators;

use ALI\Translation\Translate\PhrasePackets\TranslatePhraseCollection;
use ALI\Translation\Translate\Sources\Exceptions\SourceException;
use ALI\Translation\Translate\Sources\SourceInterface;
use function is_callable;

/**
 * Base `Translator` class.
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
     * @var bool
     */
    protected $translationFallback;

    /**
     * @var callable[]
     */
    protected $missingTranslationCallbacks = [];

    /**
     * Translate constructor
     *
     * @param $languageAlias
     * @param SourceInterface $source
     */
    public function __construct(
        $languageAlias,
        SourceInterface $source,
        $translationFallback = false
    )
    {
        $this->languageAlias = $languageAlias;
        $this->source = $source;
        $this->translationFallback = $translationFallback;
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
     * @param array $phrases
     * @return TranslatePhraseCollection
     */
    public function translateAll($phrases)
    {
        $translatePhrasePacket = new TranslatePhraseCollection();

        if ($this->isCurrentLanguageOriginal()) {
            foreach ($phrases as $phrase) {
                $translatePhrasePacket->addTranslate($phrase, null);
            }

            return $translatePhrasePacket;
        }

        $searchPhrases = array_combine($phrases, $phrases);

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

            if (!$translate && $this->translationFallback) {
                $translate = $originalPhrase;
            }
            $translatePhrasePacket->addTranslate($originalPhrase, $translate);
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
            $original,
            $translate
        );
    }

    /**
     * Delete original and all translated phrases
     * @param $original
     */
    public function delete($original)
    {
        $this->getSource()->delete($original);
    }
}
