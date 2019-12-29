<?php

namespace ALI\Translate\Sources;

use ALI\Translate\Language\LanguageInterface;

abstract class SourceAbstract implements SourceInterface
{
    /**
     * @param array             $phrases
     * @param LanguageInterface $language
     * @return array
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