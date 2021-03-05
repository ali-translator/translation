<?php

namespace ALI\Translation\Tests\unit\Translate\Sources;

use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Tests\components\SourceTester;
use ALI\Translation\Languages\Language;
use ALI\Translation\Translate\Source\Exceptions\CsvFileSource\DirectoryNotFoundException;
use ALI\Translation\Translate\Source\Exceptions\CsvFileSource\FileNotWritableException;
use ALI\Translation\Translate\Source\Exceptions\CsvFileSource\FileReadPermissionsException;
use ALI\Translation\Translate\Source\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translation\Translate\Source\Exceptions\MySqlSource\LanguageNotExistsException;
use ALI\Translation\Translate\Source\Exceptions\SourceException;
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

        $this->checkSources($originalLanguage);
        $this->checkSavedStateSources($originalLanguage);
    }

    /**
     * @param Language $originalLanguage
     * @throws SourceException
     */
    private function checkSources(Language $originalLanguage)
    {
        $sourceFactory = new SourceFactory();
        foreach ($sourceFactory->iterateAllSources($originalLanguage->getAlias()) as $source) {
            $sourceTester = new SourceTester();
            $sourceTester->testSource($source, $this);
            $source->generateInstaller()->destroy();
        }
    }

    /**
     * @param Language $originalLanguage
     * @throws SourceException
     */
    private function checkSavedStateSources(Language $originalLanguage)
    {
        $sourceFactory = new SourceFactory();
        foreach ($sourceFactory->iterateAllSources($originalLanguage->getAlias()) as $source) {
            // Save
            $source->saveOriginals(['Happy New Year!']);
            $source->saveTranslate(LanguageFactory::CURRENT_LANGUAGE_ALIAS, 'What\'s happening?', 'Що відбувається?');

            // Get new instance from saved state
            $source = $sourceFactory->regenerateSource($source, false);
            $translate = $source->getTranslate('What\'s happening?', LanguageFactory::CURRENT_LANGUAGE_ALIAS);
            $this->assertEquals('Що відбувається?', $translate);
            $this->assertEquals(['Happy New Year!'], $source->getExistOriginals(['Happy New Year!']));
            $this->assertEquals([], $source->getExistOriginals(['Happy birthday!']));

            $source->generateInstaller()->destroy();
        }
    }

    /**
     * test SourceInstaller
     */
    public function testSourceInstaller()
    {
        foreach ((new SourceFactory())->iterateAllSources(LanguageFactory::ORIGINAL_LANGUAGE_ALIAS) as $source) {
            $installer = $source->generateInstaller();

            $this->assertTrue($installer->isInstalled());
            $installer->destroy();
            $this->assertFalse($installer->isInstalled());
            $installer->install();
            $this->assertTrue($installer->isInstalled());
        }
    }

    /**
     * Test few original languages on one Source
     *
     * @throws SourceException
     * @throws DirectoryNotFoundException
     * @throws FileNotWritableException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     * @throws LanguageNotExistsException
     */
    public function testMultiOriginalLanguagesSources()
    {
        $sourceFactory = new SourceFactory();
        foreach ($sourceFactory::$allSourcesTypes as $sourceType) {
            $firstSource = $sourceFactory->generateSource($sourceType, 'en', true);
            $secondSource = $sourceFactory->generateSource($sourceType, 'de', true);

            $phraseOriginal = 'Hello';

            // Without saved original
            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);

            // With saved original
            $firstSource->saveOriginals([$phraseOriginal]);
            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $firstSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([$phraseOriginal], $existOriginals);

            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $secondSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([], $existOriginals);

            // With saved translate on first source
            $firstSource->saveTranslate('ua', $phraseOriginal, 'Привіт');
            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertEquals('Привіт', $phraseTranslate);
            $existOriginals = $firstSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([$phraseOriginal], $existOriginals);

            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $secondSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([], $existOriginals);

            // With saved translate on second source
            $secondSource->saveTranslate('ua', $phraseOriginal, 'Привіт1');
            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertEquals('Привіт1', $phraseTranslate);
            $existOriginals = $secondSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([$phraseOriginal], $existOriginals);

            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertEquals('Привіт', $phraseTranslate);
            $existOriginals = $firstSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([$phraseOriginal], $existOriginals);

            // Delete on first source
            $firstSource->delete($phraseOriginal);
            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $firstSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([], $existOriginals);

            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertEquals('Привіт1', $phraseTranslate);
            $existOriginals = $secondSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([$phraseOriginal], $existOriginals);

            // Delete on second source
            $secondSource->delete($phraseOriginal);
            $phraseTranslate = $secondSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $secondSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([], $existOriginals);

            $phraseTranslate = $firstSource->getTranslate($phraseOriginal, 'ua');
            $this->assertNull($phraseTranslate);
            $existOriginals = $firstSource->getExistOriginals([$phraseOriginal]);
            $this->assertEquals([], $existOriginals);
        }
    }
}
