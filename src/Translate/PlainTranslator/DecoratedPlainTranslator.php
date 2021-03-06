<?php

namespace ALI\Translation\Translate\PlainTranslator;

use ALI\Translation\Translate\PhraseDecorators\OriginalPhraseDecoratorManager;
use ALI\Translation\Translate\PhraseDecorators\TranslatePhraseDecoratorManager;
use ALI\Translation\Translate\PhraseCollection\TranslatePhraseCollection;

/**
 * Decorate original and translated phrases in conjunction with `Translator` class
 */
class DecoratedPlainTranslator implements PlainTranslatorInterface
{
    /**
     * @var PlainTranslatorInterface
     */
    protected $translator;

    /**
     * @var OriginalPhraseDecoratorManager
     */
    protected $originalDecoratorManager;

    /**
     * @var TranslatePhraseDecoratorManager
     */
    protected $translateDecoratorManager;

    /**
     * @param PlainTranslatorInterface $translator
     * @param OriginalPhraseDecoratorManager $originalDecoratorManager
     * @param TranslatePhraseDecoratorManager $translateDecoratorManager
     */
    public function __construct(
        PlainTranslatorInterface $translator,
        OriginalPhraseDecoratorManager $originalDecoratorManager = null,
        TranslatePhraseDecoratorManager $translateDecoratorManager = null
    )
    {
        $this->translator = $translator;
        $this->originalDecoratorManager = $originalDecoratorManager ?: new OriginalPhraseDecoratorManager();
        $this->translateDecoratorManager = $translateDecoratorManager ?: new TranslatePhraseDecoratorManager();
    }

    /**
     * @param array $phrases
     * @return TranslatePhraseCollection
     */
    public function translateAll($phrases)
    {
        foreach ($phrases as $phraseKey => $phrase) {
            $phrases[$phraseKey] = $this->originalDecoratorManager->decorate($phrase);
        }
        $translatePhrasePacket = $this->translator->translateAll($phrases);
        $decoratedTranslatedPhrasePacket = new TranslatePhraseCollection();
        foreach ($translatePhrasePacket->getAll() as $originalPhrase => $translatePhrase) {
            if ($translatePhrase) {
                $translatePhrase = $this->translateDecoratorManager->decorate($originalPhrase, $translatePhrase);
            }
            $decoratedTranslatedPhrasePacket->addTranslate($originalPhrase, $translatePhrase);
        }

        return $decoratedTranslatedPhrasePacket;
    }

    /**
     * @param string $phrase
     * @param bool $withTranslationFallback
     * @return string
     */
    public function translate($phrase, $withTranslationFallback = false)
    {
        $phrase = $this->originalDecoratorManager->decorate($phrase);
        $translate = $this->translator->translate($phrase, $withTranslationFallback);
        if ($translate) {
            $translate = $this->translateDecoratorManager->decorate($phrase, $translate);
        }

        return $translate;
    }

    /**
     * @param string $original
     * @param string $translate
     * @param null $translationLanguageAlias
     */
    public function saveTranslate($original, $translate, $translationLanguageAlias = null)
    {
        $original = $this->originalDecoratorManager->decorate($original);
        $this->translator->saveTranslate($original, $translate, $translationLanguageAlias);
    }

    /**
     * @inheritDoc
     */
    public function delete($original)
    {
        $original = $this->originalDecoratorManager->decorate($original);
        $this->translator->delete($original);
    }

    /**
     * @inheritDoc
     */
    public function isCurrentLanguageOriginal()
    {
        return $this->translator->isCurrentLanguageOriginal();
    }

    /**
     * @inheritDoc
     */
    public function getTranslationLanguageAlias()
    {
        return $this->translator->getTranslationLanguageAlias();
    }

    /**
     * @inheritDoc
     */
    public function getSource()
    {
        return $this->translator->getSource();
    }

    /**
     * @param callable $missingTranslationCallback
     */
    public function addMissingTranslationCallback(callable $missingTranslationCallback)
    {
        $this->translator->addMissingTranslationCallback($missingTranslationCallback);
    }

    /**
     * @return PlainTranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return OriginalPhraseDecoratorManager
     */
    public function getOriginalDecoratorManager()
    {
        return $this->originalDecoratorManager;
    }

    /**
     * @return TranslatePhraseDecoratorManager
     */
    public function getTranslateDecoratorManager()
    {
        return $this->translateDecoratorManager;
    }
}
