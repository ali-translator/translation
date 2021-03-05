<?php

namespace ALI\Translation\Translate;

use ALI\Translation\Translate\PhraseCollection\TranslatePhraseCollection;
use ALI\Translation\Translate\Source\Exceptions\SourceException;
use ALI\Translation\Translate\Source\SourceInterface;
use ALI\Translation\Translate\Source\SourcesCollection;

/**
 * Translator
 *
 * Class allow translation from different original languages,
 * to different translation languages
 */
class Translator
{
    /**
     * @var SourcesCollection
     */
    protected $sourceCollection;

    /**
     * @var callable[]
     */
    protected $missingTranslationCallbacks = [];

    /**
     * @param SourcesCollection $sourceCollection
     */
    public function __construct(SourcesCollection $sourceCollection)
    {
        $this->sourceCollection = $sourceCollection;
    }

    /**
     * @return SourcesCollection
     */
    public function getSourceCollection()
    {
        return $this->sourceCollection;
    }

    /**
     * @param $originalLanguageAlias
     * @param $translationLanguageAlias
     * @return SourceInterface|null
     * @throws \Exception
     */
    public function getSource($originalLanguageAlias, $translationLanguageAlias = null)
    {
        $source = $this->sourceCollection->getSource($originalLanguageAlias, $translationLanguageAlias);
        if (!$source) {
            throw new \Exception('Not found source for ' . $originalLanguageAlias . '_' . $translationLanguageAlias . ' language pair');
        }

        return $source;
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

    public function translateAll($originalLanguageAlias, $translationLanguageAlias, $phrases)
    {
        $translatePhrasePacket = new TranslatePhraseCollection();
        if ($originalLanguageAlias === $translationLanguageAlias) {
            foreach ($phrases as $phrase) {
                $translatePhrasePacket->addTranslate($phrase, null);
            }

            return $translatePhrasePacket;
        }

        $source = $this->getSource($originalLanguageAlias, $translationLanguageAlias);

        $searchPhrases = array_combine($phrases, $phrases);

        $translatesFromSource = $source->getTranslates(
            $searchPhrases,
            $translationLanguageAlias
        );

        foreach ($searchPhrases as $originalPhrase => $searchPhrase) {
            $translate = isset($translatesFromSource[$searchPhrase]) ? $translatesFromSource[$searchPhrase] : null;
            if (!$translate) {
                foreach ($this->getMissingTranslationCallbacks() as $missingTranslationCallbacks) {
                    if (is_callable($missingTranslationCallbacks)) {
                        $translate = call_user_func($missingTranslationCallbacks, $searchPhrase, $this) ?: null;
                        if ($translate) {
                            break;
                        }
                    }
                }
            }

            $translatePhrasePacket->addTranslate($originalPhrase, $translate);
        }

        return $translatePhrasePacket;
    }

    /**
     * @param string $originalLanguageAlias
     * @param string $translationLanguageAlias
     * @param string $phrase
     * @param bool $withTranslationFallback
     * @return string|null
     * @throws \Exception
     */
    public function translate(
        $originalLanguageAlias,
        $translationLanguageAlias,
        $phrase,
        $withTranslationFallback = false
    )
    {
        if ($originalLanguageAlias === $translationLanguageAlias) {
            return $phrase;
        }

        $translatePhraseCollection = $this->translateAll($originalLanguageAlias, $translationLanguageAlias, [$phrase]);

        return $translatePhraseCollection->getTranslate($phrase, $withTranslationFallback);
    }

    /**
     * @param $original
     * @param $translate
     * @param string $languageAlias
     * @throws SourceException
     */
    public function saveTranslate(
        $originalLanguageAlias,
        $translationLanguageAlias,
        $original,
        $translate
    )
    {
        $source = $this->getSource($originalLanguageAlias, $translationLanguageAlias);
        $source->saveTranslate(
            $translationLanguageAlias,
            $original,
            $translate
        );
    }

    /**
     * Delete original and all translated phrases
     * @param $original
     */
    public function delete(
        $originalLanguageAlias,
        $original,
        $translationLanguageAlias = null
    )
    {
        $this->getSource($originalLanguageAlias, $translationLanguageAlias)->delete($original);
    }
}
