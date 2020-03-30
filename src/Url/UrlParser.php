<?php

namespace ALI\Translation\Url;

use ALI\Translation\Languages\LanguageRepositoryInterface;

/**
 * Class
 */
class UrlParser
{
    /**
     * @var string[]
     */
    protected $allLanguagesAliases = [];

    /**
     * UrlHelper constructor.
     * @param string[] $allLanguagesAliases
     */
    public function __construct($allLanguagesAliases)
    {
        if ($allLanguagesAliases instanceof LanguageRepositoryInterface) {
            $languages = $allLanguagesAliases->getAll(true);
            foreach ($languages as $language) {
                $this->allLanguagesAliases[] = $language->getAlias();
            }
        } else {
            $this->allLanguagesAliases = $allLanguagesAliases;
        }
    }

    /**
     * @param string|null $requestURI
     * @return null|string
     */
    public function getLangAliasFromURI($requestURI = null)
    {
        $languageAlias = null;
        $requestURI = $this->resolveRequestUrl($requestURI);

        if (preg_match('#^/(?<language>\w{2})(?:/|\Z|\?)#', $requestURI, $parseUriMatches)) {
            if (in_array($parseUriMatches['language'], $this->allLanguagesAliases, true)) {
                $languageAlias = $parseUriMatches['language'];
            }
        }

        return $languageAlias;
    }

    /**
     * @param null $requestURI
     * @return mixed|string|string[]|null
     */
    public function getRequestUriWithoutLangAlias($requestURI = null)
    {
        $requestURI = $this->resolveRequestUrl($requestURI);
        $langFromUrl = $this->getLangAliasFromURI($requestURI);

        if (!$langFromUrl) {
            return $requestURI;
        }

        return preg_replace(
            '#^/' . preg_quote($langFromUrl, '#') . '(?:/|\Z|(\?))#Us', '/$1',
            $requestURI
        );
    }

    /**
     * @param null $requestURI
     * @return mixed|null
     * @throws \Exception
     */
    protected function resolveRequestUrl($requestURI = null)
    {
        if (is_null($requestURI) && isset($_SERVER['REQUEST_URI'])) {
            $requestURI = $_SERVER['REQUEST_URI'];
        }
        if ($requestURI === null) {
            throw new \Exception('RequestURI must be specified');
        }

        return $requestURI;
    }
}
