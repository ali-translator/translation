<?php

namespace ALI\Translation\Url;

use ALI\Translation\Languages\LanguageRepositoryInterface;

/**
 * Class
 */
class UrlParserFactory
{
    /**
     * @var string
     */
    protected $originalLanguageAlias;

    /**
     * @var LanguageRepositoryInterface
     */
    protected $languageRepository;

    /**
     * @param string $originalLanguageAlias
     * @param LanguageRepositoryInterface $languageRepository
     */
    public function __construct($originalLanguageAlias, LanguageRepositoryInterface $languageRepository)
    {
        $this->originalLanguageAlias = $originalLanguageAlias;
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
            if ($language->getAlias() === $this->originalLanguageAlias) {
                continue;
            }
            $allowedLanguagesAlis[] = $language->getAlias();
        }

        return new UrlParser($allowedLanguagesAlis);
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
