<?php

namespace ALI\Translation\Url;

/**
 * Class
 */
class UrlParser
{
    /**
     * @var string[]
     */
    protected $allowedLanguagesAlis = [];

    /**
     * @var string
     */
    protected $defaultLanguageAlias;

    /**
     * @param string[] $allowedLanguagesAlis
     * @param null|string $defaultLanguageAlias
     */
    public function __construct(array $allowedLanguagesAlis, string $defaultLanguageAlias = null)
    {
        $this->allowedLanguagesAlis = $allowedLanguagesAlis;
        $this->defaultLanguageAlias = $defaultLanguageAlias;

        if ($this->defaultLanguageAlias) {
            $defaultLanguageKey = array_search($this->defaultLanguageAlias, $this->allowedLanguagesAlis);
            if ($defaultLanguageKey) {
                unset($this->allowedLanguagesAlis[$defaultLanguageKey]);
            }
        }
    }

    /**
     * @return string
     */
    public function getDefaultLanguageAlias(): string
    {
        return $this->defaultLanguageAlias;
    }

    /**
     * @return null|string
     */
    public function getLangAliasFromURI($requestURI)
    {
        $languageAlias = null;

        if (preg_match('#^((https?:)?//[^/]+)?/(?<language>\w{2})(?:/|\Z|\?)#', $requestURI, $parseUriMatches)) {
            if (in_array($parseUriMatches['language'], $this->allowedLanguagesAlis, true)) {
                $languageAlias = $parseUriMatches['language'];
            }
        }

        if ($this->defaultLanguageAlias && $languageAlias === $this->defaultLanguageAlias) {
            return $this->defaultLanguageAlias;
        }

        return $languageAlias;
    }

    /**
     * Generate url:
     *  - change language alias for url with language alias
     *  - add language alias for url without language alias
     *  - remove language alias, if his included and input parameter is null
     *
     * @param string $requestURI
     * @param null|string $languageAlias
     * @return string|string[]|null
     */
    public function generateUrlWithLanguageAlias($requestURI, $languageAlias)
    {
        $languageAlias = $languageAlias ? (string)$languageAlias : null;
        $langFromUrl = $this->getLangAliasFromURI($requestURI);

        // Without modify
        if ($languageAlias === $langFromUrl) {
            return $requestURI;
        }

        // Need url without language alias
        if ($languageAlias === null || ($this->defaultLanguageAlias && $languageAlias === $this->defaultLanguageAlias)) {
            return $this->getRequestUriWithoutLangAlias($requestURI);
        }

        // Current url without language alis, need add
        if ($langFromUrl === null && $languageAlias !== null) {
            return preg_replace(
                '#^((https?:)?//[^/]+)?(?:/|\Z|(\?))#Us', '$1/' . $languageAlias . '/$3',
                $requestURI
            );
        }

        // Current url include language alias, change his to new language
        return preg_replace(
            '#^((https?:)?//[^/]+)?/' . preg_quote($langFromUrl, '#') . '(?:/|\Z|(\?))#Us', '$1/' . $languageAlias . '/$3',
            $requestURI
        );
    }

    /**
     * @return mixed|string|string[]|null
     */
    public function getRequestUriWithoutLangAlias($requestURI)
    {
        $langFromUrl = $this->getLangAliasFromURI($requestURI);

        if (!$langFromUrl) {
            return $requestURI;
        }

        return preg_replace(
            '#^((https?:)?//[^/]+)?/' . preg_quote($langFromUrl, '#') . '(?:/|\Z|(\?))#Us', '$1/$3',
            $requestURI
        );
    }
}
