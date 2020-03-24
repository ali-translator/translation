<?php

namespace ALI\Translation\Tests\unit\Sources;

use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Tests\components\SourceTester;
use ALI\Translation\Translate\Language\Language;
use ALI\Translation\Translate\Sources\Exceptions\SourceException;
use PHPUnit\Framework\TestCase;

/**
 * SourceTest
 */
class SourceTest extends TestCase
{
    /**
     * @throws SourceException
     */
    public function test()
    {
        $originalLanguage = (new LanguageFactory())->getOriginalLanguage();

        $this->checkMysqlSource($originalLanguage);
        $this->checkCsvSource($originalLanguage);
    }

    /**
     * @param Language $originalLanguage
     * @throws SourceException
     */
    private function checkMysqlSource(Language $originalLanguage)
    {
        $sourceFactory = new SourceFactory();

        list($mysqlSource, $mysqlSourceInstaller) = $sourceFactory->createMysqlSource($originalLanguage);

        $sourceTester = new SourceTester();
        $sourceTester->testSource($mysqlSource, $this);

        $mysqlSourceInstaller->destroy();
    }

    /**
     * @param Language $originalLanguage
     * @throws SourceException
     */
    private function checkCsvSource(Language $originalLanguage)
    {
        $sourceFactory = new SourceFactory();

        $csvSource = $sourceFactory->createCsvSource($originalLanguage);

        $sourceTester = new SourceTester();
        $sourceTester->testSource($csvSource, $this);
    }
}
