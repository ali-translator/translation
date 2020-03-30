<?php

namespace ALI\Translation\Tests\components;

use ALI\Translation\Languages\Language;
use ALI\Translation\Translate\Sources\Exceptions\SourceException;
use ALI\Translation\Translate\Sources\SourceInterface;
use PHPUnit\Framework\TestCase;

/**
 * SourceTester
 */
class SourceTester
{
    /**
     * @param SourceInterface $source
     * @param TestCase $testCase
     * @throws SourceException
     */
    public function testSource(SourceInterface $source, TestCase $testCase)
    {
        $languageForTranslate = new Language('ua', 'Ukraine');

        $originalPhrase = 'Hello';
        $translatePhrase = 'Привіт';

        $this->testSourceAddingNewTranslates($source, $testCase, $languageForTranslate, $originalPhrase, $translatePhrase);
        $this->testSourceRemovingTranslate($source, $testCase, $originalPhrase, $languageForTranslate);
        $this->testSourceAddingOriginals($source, $testCase);
    }

    /**
     * @param SourceInterface $source
     * @param TestCase $testCase
     * @param $originalPhrase
     * @param Language $languageForTranslate
     * @throws SourceException
     */
    private function testSourceRemovingTranslate(SourceInterface $source, TestCase $testCase, $originalPhrase, Language $languageForTranslate)
    {
        $source->delete($originalPhrase);
        $translatePhraseFromSource = $source->getTranslate($originalPhrase, $languageForTranslate->getAlias());

        $testCase->assertEquals('', $translatePhraseFromSource);
    }

    /**
     * @param SourceInterface $source
     * @param TestCase $testCase
     * @param Language $languageForTranslate
     * @param $originalPhrase
     * @param $translatePhrase
     * @throws SourceException
     */
    private function testSourceAddingNewTranslates(SourceInterface $source, TestCase $testCase, Language $languageForTranslate, $originalPhrase, $translatePhrase)
    {
        $source->saveTranslate($languageForTranslate->getAlias(), $originalPhrase, $translatePhrase);
        $translatePhraseFromSource = $source->getTranslate($originalPhrase, $languageForTranslate->getAlias());

        $testCase->assertEquals($translatePhrase, $translatePhraseFromSource);
    }

    /**
     * @param SourceInterface $source
     * @param TestCase $testCase
     */
    private function testSourceAddingOriginals(SourceInterface $source, TestCase $testCase)
    {
        $originals = [
            'A picture is worth 1000 words',
            'Actions speak louder than words',
            'Barking up the wrong tree',
        ];
        $source->saveOriginals($originals);

        // All originals must be exist
        $existOriginals = $source->getExistOriginals($originals);
        $testCase->assertEquals($originals, $existOriginals);

        foreach ($originals as $original) {
            $source->delete($original);
        }

        // Without originals
        $existOriginals = $source->getExistOriginals($originals);
        $testCase->assertEquals([], $existOriginals);
    }
}
