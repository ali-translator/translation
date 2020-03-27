<?php

namespace ALI\Translation\Tests\unit\Sources;

use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Tests\components\SourceTester;
use ALI\Translation\Translate\Language\Language;
use ALI\Translation\Translate\Sources\Exceptions\SourceException;
use ALI\Translation\Translate\Sources\SourceInterface;
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
        $originalLanguage = (new LanguageFactory())->createOriginalLanguage();

        $this->checkMysqlSource($originalLanguage);
        $this->checkCsvSource($originalLanguage);

        $this->checkSavedStateMysqlSource($originalLanguage);
        $this->checkSavedStateCsvSource($originalLanguage);
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

    /**
     * @param Language $originalLanguage
     * @throws SourceException
     */
    private function checkSavedStateMysqlSource(Language $originalLanguage)
    {
        $sourceFactory = new SourceFactory();

        // Save
        list($mysqlSource, $mysqlSourceInstaller) = $sourceFactory->createMysqlSource($originalLanguage);
        /** @var SourceInterface $mysqlSource */
        $mysqlSource->saveTranslate(LanguageFactory::CURRENT_LANGUAGE_ALIAS, 'What\'s happening?', 'Що відбувається?');
        $mysqlSource->saveOriginals(['Happy New Year!']);

        // Get new instance from saved state
        list($mysqlSource, $mysqlSourceInstaller) = $sourceFactory->createMysqlSource($originalLanguage);
        $translate = $mysqlSource->getTranslate('What\'s happening?', LanguageFactory::CURRENT_LANGUAGE_ALIAS);
        $this->assertEquals('Що відбувається?', $translate);
        $this->assertEquals(['Happy New Year!'], $mysqlSource->getExistOriginals(['Happy New Year!']));
        $this->assertEquals([], $mysqlSource->getExistOriginals(['Happy birthday!']));

        $mysqlSourceInstaller->destroy();
    }

    /**
     * @param Language $originalLanguage
     * @throws SourceException
     */
    private function checkSavedStateCsvSource(Language $originalLanguage)
    {
        $sourceFactory = new SourceFactory();

        // Save
        $csvSource = $sourceFactory->createCsvSource($originalLanguage);
        /** @var SourceInterface $csvSource */
        $csvSource->saveTranslate(LanguageFactory::CURRENT_LANGUAGE_ALIAS, 'What\'s happening?', 'Що відбувається?');
        $csvSource->saveOriginals(['Happy New Year!']);

        // Get new instance from saved state
        $csvSource = $sourceFactory->createCsvSource($originalLanguage);
        $translate = $csvSource->getTranslate('What\'s happening?', LanguageFactory::CURRENT_LANGUAGE_ALIAS);
        $this->assertEquals('Що відбувається?', $translate);
        $this->assertEquals(['Happy New Year!'], $csvSource->getExistOriginals(['Happy New Year!']));
        $this->assertEquals([], $csvSource->getExistOriginals(['Happy birthday!']));

        $csvSource->delete('What\'s happening?');
        $csvSource->delete('Happy New Year!');
    }
}
