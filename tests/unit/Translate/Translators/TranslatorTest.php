<?php

use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Translate\Source\Sources\FileSources\CsvSource\CsvFileSource;
use ALI\Translation\Translate\Source\Sources\MySqlSource\MySqlSource;
use ALI\Translation\Translate\Source\SourcesCollection;
use ALI\Translation\Translate\Translator;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class TranslatorTest extends TestCase
{
    /**
     * @var Translator
     */
    protected $translator;

    public function setUp(): void
    {
        parent::setUp();

        $sourceFactory = new SourceFactory();

        $sourceCollection = new SourcesCollection();
        $sourceCollection->addSource(
            $sourceFactory->generateSource(SourceFactory::SOURCE_CSV, 'en'),
            ['ua']
        );
        $sourceCollection->addSource(
            $sourceFactory->generateSource(SourceFactory::SOURCE_MYSQL, 'en')
        );
        $sourceCollection->addSource(
            $sourceFactory->generateSource(SourceFactory::SOURCE_MYSQL, 'de'),
            ['en']
        );

        $this->translator = new Translator($sourceCollection);
    }

    public function testSourceResolving()
    {
        $source = $this->translator->getSource('en', 'ua');
        $this->assertInstanceOf(CsvFileSource::class, $source);
        $source = $this->translator->getSource('en', 'ru');
        $this->assertInstanceOf(MySqlSource::class, $source);
        $source = $this->translator->getSource('en', 'de');
        $this->assertInstanceOf(MySqlSource::class, $source);
        $source = $this->translator->getSource('de', 'en');
        $this->assertInstanceOf(MySqlSource::class, $source);

        try {
            $this->translator->getSource('de', 'ua');
        } catch (Exception $exception) {
        } finally {
            $this->assertInstanceOf(Exception::class, $exception);
            unset($exception);
        }

        try {
            $this->translator->getSource('be', 'en');
        } catch (Exception $exception) {
        } finally {
            $this->assertInstanceOf(Exception::class, $exception);
            unset($exception);
        }
    }

    public function testTranslating()
    {
        $phraseOriginal = 'Hello';

        // Without saved original
        $phraseTranslate = $this->translator->translate('en', 'ua', $phraseOriginal);
        $this->assertNull($phraseTranslate);
        $phraseTranslate = $this->translator->translate('en', 'ru', $phraseOriginal);
        $this->assertNull($phraseTranslate);

        // With saved original
        $this->translator->getSource('en')->saveOriginals([$phraseOriginal]);
        $phraseTranslate = $this->translator->translate('en', 'ua', $phraseOriginal);
        $this->assertNull($phraseTranslate);
        $phraseTranslate = $this->translator->translate('en', 'ru', $phraseOriginal);
        $this->assertNull($phraseTranslate);

        // With ua translate
        $this->translator->saveTranslate('en', 'ua', $phraseOriginal, 'Привіт');
        $phraseTranslate = $this->translator->translate('en', 'ua', $phraseOriginal);
        $this->assertEquals('Привіт', $phraseTranslate);
        $phraseTranslate = $this->translator->translate('en', 'ru', $phraseOriginal);
        $this->assertNull($phraseTranslate);
        $this->translator->delete('en',$phraseOriginal,'ua');

        // With ru translate
        $this->translator->saveTranslate('en', 'ru', $phraseOriginal, 'Привет');
        $phraseTranslate = $this->translator->translate('en', 'ua', $phraseOriginal);
        $this->assertNull($phraseTranslate);
        $phraseTranslate = $this->translator->translate('en', 'ru', $phraseOriginal);
        $this->assertEquals('Привет', $phraseTranslate);
    }
}
