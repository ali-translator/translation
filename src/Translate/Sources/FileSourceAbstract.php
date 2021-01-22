<?php

namespace ALI\Translation\Translate\Sources;

/**
 * FileSourceAbstract
 */
abstract class FileSourceAbstract implements SourceInterface
{
    /**
     * @param array $phrases
     * @param string $languageAlias
     * @return array
     * @throws Exceptions\SourceException
     */
    public function getTranslates(array $phrases, $languageAlias)
    {
        $translatePhrases = [];
        foreach ($phrases as $phrase) {
            $translatePhrases[$phrase] = $this->getTranslate($phrase, $languageAlias);
        }

        return $translatePhrases;
    }

    /**
     * @return bool
     */
    public function isSensitiveForRequestsCount()
    {
        return false;
    }
}
