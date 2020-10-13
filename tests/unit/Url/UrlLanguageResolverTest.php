<?php

namespace ALI\Translation\Tests\unit\Url;

use ALI\Translation\Url\UrlLanguageResolver;
use ALI\Translation\Url\UrlParser;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class UrlLanguageResolverTest extends TestCase
{
    /**
     * Test
     */
    public function test()
    {
        $expectCurrentLanguage = 'ua';
        $expectRequestUri = '/test/?param=value';

        $_SERVER = $_SERVER ?: [];
        $_SERVER['REQUEST_URI'] = '/' . $expectCurrentLanguage . $expectRequestUri;

        $urlParser = new UrlParser([$expectCurrentLanguage]);
        $urlLanguageResolver = new UrlLanguageResolver($urlParser);

        $detectedLanguage = $urlLanguageResolver->detectLanguage($_SERVER['REQUEST_URI']);
        $this->assertEquals($expectCurrentLanguage, $detectedLanguage);

        $currentLanguageAlias = $urlLanguageResolver->resolveUrlCurrentLanguage();
        $this->assertEquals($expectRequestUri, $_SERVER['REQUEST_URI']);
        $this->assertEquals($expectCurrentLanguage, $currentLanguageAlias);
    }
}
