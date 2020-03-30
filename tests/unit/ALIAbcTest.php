<?php

namespace ALI\Translation\Tests\unit;

use ALI\Translation\ALIAbc;
use ALI\Translation\Helpers\QuickStart\ALIAbcFactory;
use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Translate\Sources\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translation\Translate\Sources\Exceptions\SourceException;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class ALIAbcTest extends TestCase
{
    /**
     * @throws UnsupportedLanguageAliasException
     * @throws SourceException
     */
    public function testTemplateWithParams()
    {
        $aliAbc = (new ALIAbcFactory())->createALIByCsvSource(SOURCE_CSV_PATH, LanguageFactory::ORIGINAL_LANGUAGE_ALIAS, LanguageFactory::CURRENT_LANGUAGE_ALIAS);
        $aliAbc->saveTranslate('Hello {objectName}!', 'Привіт {objectName}!');
        $aliAbc->saveTranslate('sun', 'сонце');

        $this->checkTranslate($aliAbc);
        $this->checkBufferTranslate($aliAbc);
        $this->checkSaveMissingOriginals($aliAbc);

        $aliAbc->deleteOriginal('Hello {objectName}!');
        $aliAbc->deleteOriginal('sun');
    }

    /**
     * @param ALIAbc $aliAbc
     */
    private function checkTranslate(ALIAbc $aliAbc)
    {
        $translated = $aliAbc->translate('Hello {objectName}!', [
            'objectName' => 'sun',
        ]);
        $this->assertEquals('Привіт sun!', $translated);
    }

    /**
     * @param ALIAbc $aliAbc
     */
    private function checkBufferTranslate(ALIAbc $aliAbc)
    {
        $content = '<div>' . $aliAbc->addToBuffer('Hello {objectName}!', [
                'objectName' => 'sun',
            ]) . '</div>';
        $translated = $aliAbc->translateBuffer($content);
        $this->assertEquals('<div>Привіт sun!</div>', $translated);
    }

    /**
     * @param ALIAbc $aliAbc
     */
    private function checkSaveMissingOriginals(ALIAbc $aliAbc)
    {
        $missingPhrase = 'Some missing phrase';
        $this->assertEquals([], $aliAbc->getTranslator()->getSource()->getExistOriginals([$missingPhrase]));
        $aliAbc->translate($missingPhrase);
        $aliAbc->saveOriginalsWithoutTranslates();
        $this->assertEquals([$missingPhrase], $aliAbc->getTranslator()->getSource()->getExistOriginals([$missingPhrase]));
        $aliAbc->getTranslator()->getSource()->delete($missingPhrase);
        $this->assertEquals([], $aliAbc->getTranslator()->getSource()->getExistOriginals([$missingPhrase]));
    }
}
