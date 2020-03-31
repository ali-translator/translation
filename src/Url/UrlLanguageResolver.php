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
            $currentLanguageAlias = $this->urlParser->getLangAliasFromURI($_SERVER['REQUEST_URI']);
            if ($currentLanguageAlias === null) {
                $this->_currentLanguageAlias = $this->urlParser->getDefaultLanguageAlias();
            } else {
                $this->_currentLanguageAlias = $currentLanguageAlias;
            }
            $_SERVER['REQUEST_URI'] = $this->urlParser->getRequestUriWithoutLangAlias($_SERVER['REQUEST_URI']);
        }

        return (string)$this->_currentLanguageAlias;
    }
}
