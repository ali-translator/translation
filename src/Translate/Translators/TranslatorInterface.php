<?php

namespace ALI\Translation\Translate\Translators;

use ALI\Translation\Translate\Language\LanguageInterface;
use ALI\Translation\Translate\PhrasePackets\TranslatePhrasePacket;
use ALI\Translation\Translate\Sources\SourceInterface;

/**
 * TranslatorInterface
 */
interface TranslatorInterface
{
    /**
     * @param array $phrases
     * @return TranslatePhrasePacket
     */
    public function translateAll($phrases);

    /**
     * @param string $phrase
     * @return string
     */
    public function translate($phrase);

    /**
     * @return bool
     */
    public function isCurrentLanguageOriginal();

    /**
     * @return string
     */
    public function getLanguageAlias();

    /**
     * @return SourceInterface
     */
    public function getSource();

    /**
     * @param callable $missingTranslationCallback
     */
    public function addMissingTranslationCallback(callable $missingTranslationCallback);
}
