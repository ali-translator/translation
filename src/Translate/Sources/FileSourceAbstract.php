<?php

namespace ALI\Translation\Translate\Sources;

use ALI\Translation\Translate\Language\LanguageInterface;

/**
 * FileSourceAbstract
 */
abstract class FileSourceAbstract implements SourceInterface
{
    /**
     * @param array $phrases
     * @param LanguageInterface $language
     * @return array
     * @throws Exceptions\SourceException
     */
    public function getTranslates(array $phrases, LanguageInterface $language)
    {
        $translatePhrases = [];
        foreach ($phrases as $phrase) {
            $translatePhrases[$phrase] = $this->getTranslate($phrase, $language);
        }

        return $translatePhrases;
    }
}
