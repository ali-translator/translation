<?php

namespace ALI\Translation\Translate\Translators;

use ALI\Translation\Translate\PhrasePackets\TranslatePhraseCollection;
use ALI\Translation\Translate\Sources\Exceptions\SourceException;
use ALI\Translation\Translate\Sources\SourceInterface;
use function is_callable;

/**
 * PlainTranslator
 * with one selected "original" language
 * and one selected "translation" language
 */
class PlainTranslator implements PlainTranslatorInterface
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
     * Translate constructor
     *
     * @param $languageAlias
     * @param SourceInterface $source
     */
    public function __construct(
        $languageAlias,
        SourceInterface $source
    )
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
     * Fast translate without buffers and processors
     *
     * @param string $phrase
     * @param bool $withTranslationFallback
     * @return string|null
     */
    public function translate($phrase, $withTranslationFallback = false)
    {
        $translatePhraseCollection = $this->translateAll([$phrase]);

        return $translatePhraseCollection->getTranslate($phrase, $withTranslationFallback);
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
