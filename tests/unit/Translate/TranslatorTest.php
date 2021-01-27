<?php

namespace ALI\Translation\Tests\unit\Translate;

use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Translate\Sources\CsvFileSource;
use ALI\Translation\Translate\Sources\Exceptions\SourceException;
use ALI\Translation\Translate\Translators\Translator;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class TranslatorTest extends TestCase
{
    /**
     * @throws SourceException
     */
    public function testTranslationFallback()
    {
        $source = (new SourceFactory())->createCsvSource(LanguageFactory::ORIGINAL_LANGUAGE_ALIAS);

        $originalPhrase = 'Some test phrase';
        $translatedPhrase = 'Деяка тестова фраза';

        $this->checkTranslationWithoutFallback($source, $originalPhrase, $translatedPhrase);
        $this->checkTranslationFallback($source, $originalPhrase, $translatedPhrase);
    }

    /**
     * @param CsvFileSource $source
     * @param $originalPhrase
     * @param $translatedPhrase
     * @throws SourceException
     */
    private function checkTranslationWithoutFallback(CsvFileSource $source, $originalPhrase, $translatedPhrase)
    {
        $translator = new Translator(
            LanguageFactory::CURRENT_LANGUAGE_ALIAS,
            $source
        );

        $this->assertEquals($translator->translate($originalPhrase), '');
        $translator->saveTranslate($originalPhrase, $translatedPhrase);
        $this->assertEquals($translator->translate($originalPhrase), $translatedPhrase);
        $translator->delete($originalPhrase);
    }


    /**
     * @param CsvFileSource $source
     * @param $originalPhrase
     * @param $translatedPhrase
     * @throws SourceException
     */
    private function checkTranslationFallback(CsvFileSource $source, $originalPhrase, $translatedPhrase)
    {
        $translator = new Translator(
            LanguageFactory::CURRENT_LANGUAGE_ALIAS,
            $source
        );

        $this->assertEquals($originalPhrase, $translator->translate($originalPhrase, true));
        $translator->saveTranslate($originalPhrase, $translatedPhrase);
        $this->assertEquals($translatedPhrase,$translator->translate($originalPhrase,true));
        $translator->delete($originalPhrase);
    }
}
