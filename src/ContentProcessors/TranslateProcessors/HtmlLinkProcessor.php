<?php

namespace ALI\Translation\ContentProcessors\TranslateProcessors;

use ALI\Translation\Translate\Translators\PlainTranslatorInterface;

/**
 * You may use this processor if you want to store information about language in URL.
 * Processor replace all links to links with current language (/about/ -> /ru/about/)
 * You can use " % " after html tag name for skipping URL replacing (<a % href="/test">test</a>)
 * Class HtmlLinkProcessor
 * @package ALI\Translation\Processors\TranslateProcessors
 */
class HtmlLinkProcessor implements TranslateProcessors
{
    /**
     * Usually $_SERVER['HTTP_HOST']
     * @var string
     */
    protected $currentHttpHost;

    /**
     * Attributes where we can find URLs ('href', 'action', 'src')
     * @var array
     */
    protected $attributesWithLinks = [];

    /**
     * Allowed to change URLs with file extensions ('html', 'php')
     * @var array
     */
    protected $allowedExtensions = [];

    /**
     * HtmlLinkProcessor constructor.
     * @param string $currentHttpHost
     * @param array  $attributesWithLinks
     * @param array  $allowedExtensions
     */
    public function __construct(
        $currentHttpHost,
        array $attributesWithLinks = ['href', 'action'],
        array $allowedExtensions = ['html', 'php']
    ) {
        $this->currentHttpHost = $currentHttpHost;
        $this->attributesWithLinks = $attributesWithLinks;
        $this->allowedExtensions = $allowedExtensions;
    }

    /**
     * @return string
     */
    public function getCurrentHttpHost()
    {
        return $this->currentHttpHost;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    /**
     * @return array
     */
    public function getAttributesWithLinks()
    {
        return $this->attributesWithLinks;
    }

    /**
     * @param string $content
     * @param string $cleanContent
     * @param PlainTranslatorInterface $translator
     * @return string
     */
    public function process($content, $cleanContent, PlainTranslatorInterface $translator)
    {
        if ($translator->isCurrentLanguageOriginal()) {
            $content = $this->removeExceptionMark($content);
            return $content;
        }

        $attributesRegex = [];
        $attributes = $this->getAttributesWithLinks();
        foreach ($attributes as $attribute) {
            $attributesRegex[] = '(?:' . preg_quote($attribute) . ')';
        }

        $content = preg_replace_callback(
            '$
                (?<start><\w+       #Html tag
                    (?!\s\%\s)      #Skip exceptions
                    (?:[^>]*)\s     #Any attributes
                    (?:' . implode('|', $attributesRegex) . ')
                        =
                    ("|\')
                )
                    (?<url>.+)
                (?<end>
                    (?!\\\)\\2
                )
                $Usix',
            function ($matches) use ($translator) {
                return $matches['start'] . $this->getLocalizedUrl($matches['url'], $translator) . $matches['end'];
            },
            $content
        );

        $content = $this->removeExceptionMark($content);

        return $content;
    }

    /**
     * Get localized URL (only URL starts from /, //, https://, http://)
     *
     * @param string $url
     * @param PlainTranslatorInterface $translator
     * @return string
     */
    public function getLocalizedUrl($url, PlainTranslatorInterface $translator)
    {
        $languageAlias = $translator->getTranslationLanguageAlias();

        if ($translator->isCurrentLanguageOriginal()) {
            return $url;
        }

        if (!$this->isUrlHasAllowedExtension($url)) {
            return $url;
        }

        //fix for path that start from "./"
        if (strpos($url, './') === 0) {
            $url = substr($url, 1);
        }

        return preg_replace(
            '#^
                (?:
                    (?:(?:https?:)?//' . preg_quote($this->getCurrentHttpHost()) . '/)
                    |
                    (?:/(?!/))
                )
                (?!' . preg_quote($languageAlias) . '(?:/|\Z))#Uixs',
            '$0' . $languageAlias . '/', $url
        );
    }

    /**
     * @param string $buffer
     * @return string
     */
    protected function removeExceptionMark($buffer)
    {
        $buffer = preg_replace('#(<\w+)\s%\s#', '$1 ', $buffer);

        return $buffer;
    }

    /**
     * @param $url
     * @return bool
     */
    protected function isUrlHasAllowedExtension($url)
    {
        $isAllowed = true;
        $extension = $this->getExtensionFromUrl($url);

        if ($extension) {
            $isAllowed = in_array(strtolower($extension), $this->getAllowedExtensions(), true);
        }

        return $isAllowed;
    }

    /**
     * @param $url
     * @return null|string
     */
    protected function getExtensionFromUrl($url)
    {
        $extension = null;

        if (preg_match('@(?:\w|/|\A)/.*\.([a-z]+)(?:#|\?|\Z)@iU', $url, $math)) {
            $extension = $math[1];
        }

        return $extension;
    }
}
