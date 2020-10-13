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
     * @param string $defaultLanguageAlias
     */
    public function __construct(UrlParser $urlParser)
    {
        $this->urlParser = $urlParser;
    }

    /**
     * @var bool|null|string
     */
    protected $_currentLanguageAlias = false;

    /**
     * @return string|null
     */
    public function resolveUrlCurrentLanguage()
    {
        if ($this->_currentLanguageAlias === false) {
            $this->_currentLanguageAlias = $this->detectLanguage($_SERVER['REQUEST_URI']);
            $_SERVER['REQUEST_URI'] = $this->urlParser->getRequestUriWithoutLangAlias($_SERVER['REQUEST_URI']);
        }

        return (string)$this->_currentLanguageAlias;
    }

    /**
     * @param $url
     * @return string|null
     */
    public function detectLanguage($url)
    {
        $currentLanguageAlias = $this->urlParser->getLangAliasFromURI($url);
        if ($currentLanguageAlias === null) {
            $currentLanguageAlias = $this->urlParser->getDefaultLanguageAlias();
        }

        return $currentLanguageAlias;
    }
}
