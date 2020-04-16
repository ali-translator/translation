<?php

namespace ALI\Translation\Tests\unit\Translate\MissingTranslateCallbacks;

use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Translate\MissingTranslateCallbacks\CollectorMissingTranslatesCallback;
use ALI\Translation\Translate\Sources\Exceptions\CsvFileSource\DirectoryNotFoundException;
use ALI\Translation\Translate\Sources\Exceptions\CsvFileSource\FileNotWritableException;
use ALI\Translation\Translate\Sources\Exceptions\CsvFileSource\FileReadPermissionsException;
use ALI\Translation\Translate\Sources\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translation\Translate\Translators\Translator;
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
        $source = (new SourceFactory())->createCsvSource($originalLanguage->getAlias());

        $currentLanguage = (new LanguageFactory())->createCurrentLanguage();
        $translator = new Translator($currentLanguage->getAlias(), $source);

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
        $this->assertEquals(['Test'], $callBack->getOriginalPhrasePacket()->getAll());
        $this->assertTrue($callBack->getOriginalPhrasePacket()->exist('Test'));
        $this->assertFalse($callBack->getOriginalPhrasePacket()->exist('Test new'));
    }
}
