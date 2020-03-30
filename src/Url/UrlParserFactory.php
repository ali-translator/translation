<?php

namespace ALI\Translation\Url;

use ALI\Translation\ALIAbc;
use ALI\Translation\Languages\LanguageRepositoryInterface;
use ALI\Translation\Translate\Sources\SourceInterface;

/**
 * Class
 */
class UrlParserFactory
{
    /**
     * @var ALIAbc
     */
    protected $aliAbc;

    /**
     * @var LanguageRepositoryInterface
     */
    protected $languageRepository;

    /**
     * @param ALIAbc $aliAbc
     * @param LanguageRepositoryInterface $languageRepository
     */
    public function __construct(ALIAbc $aliAbc, LanguageRepositoryInterface $languageRepository)
    {
        $this->aliAbc = $aliAbc;
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
            if ($language->getAlias() === $this->aliAbc->getOriginalLanguageAlias()) {
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
