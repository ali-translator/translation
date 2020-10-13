<?php

namespace ALI\Translation\Tests\unit\Helpers;

use ALI\Translation\ALIAbc;
use ALI\Translation\Helpers\QuickStart\ALIAbcFactory;
use ALI\Translation\Tests\components\Factories\LanguageFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class ALIAbcFactoryTest extends TestCase
{
    /**
     * Test
     */
    public function test()
    {
        $originalLanguageAlias = LanguageFactory::ORIGINAL_LANGUAGE_ALIAS;
        $currentLanguageAlias = LanguageFactory::CURRENT_LANGUAGE_ALIAS;

        $connection = new \PDO(SOURCE_MYSQL_DNS, SOURCE_MYSQL_USER, SOURCE_MYSQL_PASSWORD);

        $quickStart = new ALIAbcFactory();

        $csvSourceALIAb = $quickStart->createALIByCsvSource(SOURCE_CSV_PATH, $originalLanguageAlias, $currentLanguageAlias);
        $this->assertInstanceOf(ALIAbc::class, $csvSourceALIAb);

        $htmlBufferCsvSourceALIAb = $quickStart->createALIByHtmlBufferCsvSource(SOURCE_CSV_PATH, $originalLanguageAlias, $currentLanguageAlias, 'https://test.com/en/');
        $this->assertInstanceOf(ALIAbc::class, $htmlBufferCsvSourceALIAb);

        $mysqlSourceALIAb = $quickStart->createALIByMysqlSource($connection, $originalLanguageAlias, $currentLanguageAlias);
        $this->assertInstanceOf(ALIAbc::class, $mysqlSourceALIAb);

        $htmlBufferMysqlSource = $quickStart->createALIByHtmlBufferMysqlSource($connection, $originalLanguageAlias, $currentLanguageAlias, 'https://test.com/en/');
        $this->assertInstanceOf(ALIAbc::class, $htmlBufferMysqlSource);
    }
}
