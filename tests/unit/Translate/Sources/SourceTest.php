<?php

namespace ALI\Translation\Tests\unit\Translate\Sources;

use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Tests\components\SourceTester;
use ALI\Translation\Languages\Language;
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
}
