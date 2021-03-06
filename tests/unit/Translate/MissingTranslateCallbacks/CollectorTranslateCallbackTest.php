<?php

namespace ALI\Translation\Tests\unit\Translate\MissingTranslateCallbacks;

use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Translate\MissingTranslateCallbacks\CollectorMissingTranslatesCallback;
use ALI\Translation\Translate\Source\Exceptions\CsvFileSource\DirectoryNotFoundException;
use ALI\Translation\Translate\Source\Exceptions\CsvFileSource\FileNotWritableException;
use ALI\Translation\Translate\Source\Exceptions\CsvFileSource\FileReadPermissionsException;
use ALI\Translation\Translate\Source\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translation\Translate\PlainTranslator\PlainTranslator;
use ALI\Translation\Translate\PlainTranslator\PlainTranslatorFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class CollectorTranslateCallbackTest extends TestCase
{
    /**
     * @throws DirectoryNotFoundException
     * @throws FileNotWritableException
     * @throws FileReadPermissionsException
     * @throws UnsupportedLanguageAliasException
     */
    public function test()
    {
        $originalLanguage = (new LanguageFactory())->createOriginalLanguage();
        foreach ((new SourceFactory())->iterateAllSources($originalLanguage->getAlias()) as $source) {
            $currentLanguage = (new LanguageFactory())->createCurrentLanguage();
            $translator = (new PlainTranslatorFactory())->createPlainTranslator($source, $currentLanguage->getAlias());

            $callBack = new CollectorMissingTranslatesCallback();

            $translator->addMissingTranslationCallback($callBack);

            $translatePhrase = $translator->translate('Test');
            $this->assertEquals('', $translatePhrase);

            // Add translate
            $source->saveTranslate($currentLanguage->getAlias(), 'Cat', 'Кіт');
            $translatePhrase = $translator->translate('Cat');
            $source->delete('Cat');
            $this->assertEquals('Кіт', $translatePhrase);

            // Test one phrase without translate
            $this->assertEquals(['Test'], $callBack->getOriginalPhraseCollection()->getAll());
            $this->assertTrue($callBack->getOriginalPhraseCollection()->exist('Test'));
            $this->assertFalse($callBack->getOriginalPhraseCollection()->exist('Test new'));
        }
    }
}
