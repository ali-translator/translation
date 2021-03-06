<?php

namespace ALI\Translation\Tests\unit;

use ALI\Translation\ALIAbc;
use ALI\Translation\Helpers\QuickStart\ALIAbcFactory;
use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Translate\Source\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translation\Translate\Source\Exceptions\SourceException;
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
     * @throws SourceException
     * @throws UnsupportedLanguageAliasException
     */
    public function testBufferTranslateHtmlEncoding()
    {
        $aliAbc = (new ALIAbcFactory())->createALIByCsvSource(SOURCE_CSV_PATH, LanguageFactory::ORIGINAL_LANGUAGE_ALIAS, LanguageFactory::CURRENT_LANGUAGE_ALIAS);
        $originalPhrase = 'Hi <br> bro!';

        // Original without fallback, without encoding
        $this->assertEquals('', $aliAbc->translate($originalPhrase));
        // Original without fallback, without encoding
        $this->assertEquals('Hi <br> bro!', $aliAbc->translateWithFallback($originalPhrase));
        // Original, with fallback, with encoding
        $content = '<div>' . $aliAbc->addToBuffer($originalPhrase) . '</div>';
        $this->assertEquals('<div>Hi <br> bro!</div>', $aliAbc->translateBuffer($content));

        // With translate
        $aliAbc->saveTranslate($originalPhrase, 'Привіт <br> бро!');
        $content = '<div>' . $aliAbc->addToBuffer($originalPhrase) . '</div>';
        $this->assertEquals('<div>Привіт &lt;br&gt; бро!</div>', $aliAbc->translateBuffer($content));
        $aliAbc->deleteOriginal($originalPhrase);
    }

    /**
     * @throws SourceException
     * @throws UnsupportedLanguageAliasException
     */
    public function testTranslateContentWithProcessors()
    {
        $aliAbc = (new ALIAbcFactory())->createALIByHtmlBufferCsvSource(SOURCE_CSV_PATH, LanguageFactory::ORIGINAL_LANGUAGE_ALIAS, LanguageFactory::CURRENT_LANGUAGE_ALIAS, 'https://test.com/en/');
        $aliAbc->saveTranslate('sun', 'сонце');

        $content = '<div>sun</div>';
        $translated = $aliAbc->translateBuffer($content);
        $this->assertEquals('<div>сонце</div>', $translated);

        $aliAbc->deleteOriginal('sun');
    }

    /**
     * Check "translateAll" method
     */
    public function testCheckTranslateAll()
    {
        $aliAbc = (new ALIAbcFactory())->createALIByCsvSource(SOURCE_CSV_PATH, LanguageFactory::ORIGINAL_LANGUAGE_ALIAS, LanguageFactory::CURRENT_LANGUAGE_ALIAS);

        $translates = $aliAbc->translateAll(['Hello!', 'Bye!']);
        $this->assertEquals(['Hello!' => null, 'Bye!' => null], $translates->getAll());

        $aliAbc->saveTranslate('Hello!', 'Привіт!');
        $translates = $aliAbc->translateAll(['Hello!', 'Bye!']);
        $this->assertEquals(['Hello!' => 'Привіт!', 'Bye!' => null], $translates->getAll());

        $aliAbc->deleteOriginal('Hello!');

        $translates = $aliAbc->translateAll(['Hello!', 'Bye!']);
        $this->assertEquals(['Hello!' => null, 'Bye!' => null], $translates->getAll());
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
        $this->assertEquals([], $aliAbc->getPlainTranslator()->getSource()->getExistOriginals([$missingPhrase]));
        $aliAbc->translate($missingPhrase);
        $aliAbc->saveMissedOriginals();
        $this->assertEquals([$missingPhrase], $aliAbc->getPlainTranslator()->getSource()->getExistOriginals([$missingPhrase]));
        $aliAbc->getPlainTranslator()->getSource()->delete($missingPhrase);
        $this->assertEquals([], $aliAbc->getPlainTranslator()->getSource()->getExistOriginals([$missingPhrase]));
    }
}
