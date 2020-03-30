<?php

namespace ALI\Translation\Url;

/**
 * Class
 */
class UrlLanguageResolver
{
    /**
     * @var UrlParser
     */
    protected $urlParser;

    /**
     * @param UrlParser $urlParser
     */
    public function __construct(UrlParser $urlParser)
    {
        $this->urlParser = $urlParser;
    }

    /**
     * @var bool|null|string
     */
    protected $currentLanguageAlias = false;

    /**
     * @return string|null
     */
    public function resolveUrlCurrentLanguage()
    {
        if ($this->currentLanguageAlias === false) {
            $this->currentLanguageAlias = $this->urlParser->getLangAliasFromURI($_SERVER['REQUEST_URI']);
            $_SERVER['REQUEST_URI'] = $this->urlParser->getRequestUriWithoutLangAlias($_SERVER['REQUEST_URI']);
        }

        return $this->currentLanguageAlias;
    }
}
