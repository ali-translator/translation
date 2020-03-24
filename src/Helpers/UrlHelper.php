<?php

namespace ALI\Translation\Helpers;

use function in_array;
use function is_null;
use function preg_match;
use function preg_quote;
use function preg_replace;

/**
 * Class UrlHelper
 * @package ALI\Translation\Helpers
 */
class UrlHelper
{
    protected $requestURI;
    protected $allLanguagesAliases;

    /**
     * UrlHelper constructor.
     * @param array  $allLanguagesAliases
     * @param string $requestURI
     */
    public function __construct($allLanguagesAliases, $requestURI = null)
    {
        if (is_null($requestURI) && isset($_SERVER['REQUEST_URI'])) {
            $requestURI = $_SERVER['REQUEST_URI'];
        }
        $this->requestURI = $requestURI;
        $this->allLanguagesAliases = $allLanguagesAliases;
    }

    /**
     * @return bool|string
     */
    public function getLangAliasFromURI()
    {
        $languageAlias = false;
        if (preg_match('#^/(?<language>\w{2})(?:/|\Z|\?)#', $this->requestURI, $parseUriMatches)) {
            if (in_array($parseUriMatches['language'], $this->allLanguagesAliases, true)) {
                $languageAlias = $parseUriMatches['language'];
            }
        }

        return $languageAlias;
    }

    /**
     * @return string
     */
    public function getRequestUriWithoutLangAlias()
    {
        $langFromUrl = $this->getLangAliasFromURI();
        if (!$langFromUrl) {
            return $this->requestURI;
        }

        return preg_replace(
            '#^/' . preg_quote($langFromUrl, '#') . '(?:/|\Z|(\?))#Us', '/$1',
            $this->requestURI
        );
    }
}
