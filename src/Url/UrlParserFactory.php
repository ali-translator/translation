<?php

namespace ALI\Translation\Url;

use ALI\Translation\Languages\LanguageRepositoryInterface;
use ALI\Translation\Translate\Sources\SourceInterface;

/**
 * Class
 */
class UrlParserFactory
{
    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * @var LanguageRepositoryInterface
     */
    protected $languageRepository;

    /**
     * @param SourceInterface $source
     * @param LanguageRepositoryInterface $languageRepository
     */
    public function __construct(SourceInterface $source, LanguageRepositoryInterface $languageRepository)
    {
        $this->source = $source;
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
            if ($language->getAlias() === $this->source->getOriginalLanguageAlias()) {
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
