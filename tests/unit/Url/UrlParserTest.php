<?php

namespace unit\Url;

use ALI\Translation\Url\UrlParser;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class UrlParserTest extends TestCase
{
    /**
     * @var UrlParser
     */
    private $urlParser;

    /**
     * @var array
     */
    private $hosts = [null, 'http://some.domain.com', 'https://some.domain.com'];

    /**
     * This method is called before the first test of this test class is run.
     */
    public function setUp(): void
    {
        $this->urlParser = new UrlParser(['ua','en']);
    }

    /**
     * Test language detecting from url
     */
    public function testGetLangAliasFromURI()
    {
        foreach ($this->hosts as $host) {
            $languageAlias = $this->urlParser->getLangAliasFromURI($host . '/test/');
            $this->assertEquals(null, $languageAlias);

            $languageAlias = $this->urlParser->getLangAliasFromURI($host . '/test');
            $this->assertEquals(null, $languageAlias);

            $languageAlias = $this->urlParser->getLangAliasFromURI($host . '/test/?para=value&param[]=1');
            $this->assertEquals(null, $languageAlias);

            $languageAlias = $this->urlParser->getLangAliasFromURI($host . '/ru/test/');
            $this->assertEquals(null, $languageAlias);

            $languageAlias = $this->urlParser->getLangAliasFromURI($host . '/ua/test/');
            $this->assertEquals('ua', $languageAlias);

            $languageAlias = $this->urlParser->getLangAliasFromURI($host . '/ua/test');
            $this->assertEquals('ua', $languageAlias);
        }
    }

    /**
     * Test
     */
    public function testGetRequestUriWithoutLangAlias()
    {
        foreach ($this->hosts as $host) {
            $urlWithoutLanguage = $this->urlParser->getRequestUriWithoutLangAlias($host . '/test/');
            $this->assertEquals($host . '/test/', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->getRequestUriWithoutLangAlias($host . '/test');
            $this->assertEquals($host . '/test', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->getRequestUriWithoutLangAlias($host . '/test/?para=value&param[]=1');
            $this->assertEquals($host . '/test/?para=value&param[]=1', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->getRequestUriWithoutLangAlias($host . '/ru/test/');
            $this->assertEquals($host . '/ru/test/', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->getRequestUriWithoutLangAlias($host . '/ua/test/');
            $this->assertEquals($host . '/test/', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->getRequestUriWithoutLangAlias($host . '/ua/test');
            $this->assertEquals($host . '/test', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->getRequestUriWithoutLangAlias($host . '/ua/test/?para=value&param[]=1');
            $this->assertEquals($host . '/test/?para=value&param[]=1', $urlWithoutLanguage);
        }
    }

    /**
     * Test
     */
    public function testGenerateUrlWithLanguageAlias()
    {
        foreach ($this->hosts as $host) {
            // Remove
            $urlWithoutLanguage = $this->urlParser->generateUrlWithLanguageAlias($host . '/test/', null);
            $this->assertEquals($host . '/test/', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->generateUrlWithLanguageAlias($host . '/ru/test/', null);
            $this->assertEquals($host . '/ru/test/', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->generateUrlWithLanguageAlias($host . '/ua/test/', null);
            $this->assertEquals($host . '/test/', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->generateUrlWithLanguageAlias($host . '/ua/test/?para=value&param[]=1', null);
            $this->assertEquals($host . '/test/?para=value&param[]=1', $urlWithoutLanguage);

            // Add
            $urlWithoutLanguage = $this->urlParser->generateUrlWithLanguageAlias($host . '/test/', 'ua');
            $this->assertEquals($host . '/ua/test/', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->generateUrlWithLanguageAlias($host . '/ua/test/', 'ua');
            $this->assertEquals($host . '/ua/test/', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->generateUrlWithLanguageAlias($host . '/test/?para=value&param[]=1', 'ua');
            $this->assertEquals($host . '/ua/test/?para=value&param[]=1', $urlWithoutLanguage);

            // Change
            $urlWithoutLanguage = $this->urlParser->generateUrlWithLanguageAlias($host . '/en/test/', 'ua');
            $this->assertEquals($host . '/ua/test/', $urlWithoutLanguage);

            $urlWithoutLanguage = $this->urlParser->generateUrlWithLanguageAlias($host . '/en/test/?para=value&param[]=1', 'ua');
            $this->assertEquals($host . '/ua/test/?para=value&param[]=1', $urlWithoutLanguage);
        }
    }
}
