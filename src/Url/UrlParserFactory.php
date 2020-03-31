<?php

namespace ALI\Translation\Url;

use ALI\Translation\Languages\LanguageRepositoryInterface;

/**
 * Class
 */
class UrlParserFactory
{
    /**
     * Default site language alias (on this language, site url has not language alias)
     *
     * @var string
     */
    protected $defaultLanguageAlias;

    /**
     * @var LanguageRepositoryInterface
     */
    protected $languageRepository;

    /**
     * @param string $defaultLanguageAlias
     * @param LanguageRepositoryInterface $languageRepository
     */
    public function __construct($defaultLanguageAlias, LanguageRepositoryInterface $languageRepository)
    {
        $this->defaultLanguageAlias = $defaultLanguageAlias;
        $this->languageRepository = $languageRepository;
    }

    /**
     * @return UrlParser
     */
    public function createParser()
    {
        $languages = $this->languageRepository->getAll(true);

        $allowedLanguagesAlis = [];
        foreach ($languages as $language) {
            if ($language->getAlias() === $this->defaultLanguageAlias) {
                continue;
            }
            $allowedLanguagesAlis[] = $language->getAlias();
        }

        return new UrlParser($allowedLanguagesAlis, $this->defaultLanguageAlias);
    }

    /**
     * @return UrlLanguageResolver
     */
    public function createUrlLanguageResolver()
    {
        $urlParser = $this->createParser();

        return new UrlLanguageResolver($urlParser);
    }
}
