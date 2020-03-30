<?php

namespace ALI\Translation\Url;

use ALI\Translation\Languages\LanguageRepositoryInterface;

/**
 * Class
 */
class UrlParserFactory
{
    /**
     * @param LanguageRepositoryInterface $languageRepository
     * @param $defaultLanguageAlias
     * @return UrlParser
     */
    public function createParser(LanguageRepositoryInterface $languageRepository, $defaultLanguageAlias)
    {
        $languages = $languageRepository->getAll(true);

        $allowedLanguagesAlis = [];
        foreach ($languages as $language) {
            if ($language->getAlias() === $defaultLanguageAlias) {
                continue;
            }
            $allowedLanguagesAlis[] = $language->getAlias();
        }

        return new UrlParser($allowedLanguagesAlis);
    }
}
