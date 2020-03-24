<?php

namespace ALI\Translation\Translate\Sources;

use ALI\Translation\Translate\Language\LanguageInterface;
use ALI\Translation\Translate\Sources\Exceptions\SourceException;

/**
 * SourceInterface Interface
 */
interface SourceInterface
{
    /**
     * @return LanguageInterface
     */
    public function getOriginalLanguage();

    /**
     * @param string            $phrase
     * @param LanguageInterface $language
     * @return string
     * @throws SourceException
     */
    public function getTranslate($phrase, LanguageInterface $language);

    /**
     * Get an array with original phrases as a key
     * and translated into a value
     * @param array             $phrases
     * @param LanguageInterface $language
     * @return array
     */
    public function getTranslates(array $phrases, LanguageInterface $language);

    /**
     * @param LanguageInterface $language
     * @param string            $original
     * @param string            $translate
     * @throws SourceException
     */
    public function saveTranslate(LanguageInterface $language, $original, $translate);

    /**
     * Delete original and all translated phrases
     * @param string $original
     */
    public function delete($original);
}
