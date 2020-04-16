<?php

namespace ALI\Translation\Tests\unit\Buffer;

use ALI\Translation\Buffer\BufferCaptcher;
use ALI\Translation\Buffer\BufferContent;
use ALI\Translation\Buffer\BufferTranslate;
use ALI\Translation\ContentProcessors\ContentProcessorsManager;
use ALI\Translation\ContentProcessors\TranslateProcessors\CustomTagProcessor;
use ALI\Translation\ContentProcessors\TranslateProcessors\SimpleTextProcessor;
use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Languages\Language;
use ALI\Translation\Translate\Sources\Exceptions\SourceException;
use ALI\Translation\Translate\Sources\SourceInterface;
use ALI\Translation\Translate\Translators\Translator;
use ALI\Translation\Translate\Translators\TranslatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * BufferTranslateTest
 */
class BufferTranslateTest extends TestCase
{
    /**
     * @throws SourceException
     */
    public function test()
    {
        list($originalLanguage, $currentLanguage) = (new LanguageFactory())->createOriginalAndCurrentLanguage();

        $sourceFactory = new SourceFactory();
        $source = $sourceFactory->createCsvSource($originalLanguage->getAlias());

        $translator = new Translator($currentLanguage->getAlias(), $source);

        $this->checkTranslateBufferWithoutTranslatedPhrase($translator);

        $this->checkTranslateBuffer($source, $currentLanguage, $translator);

        $this->checkTranslateBuffersWithProcessors($source, $currentLanguage, $translator);

        $this->checkTranslateBuffersWithProcessorsByOneRequest($source, $currentLanguage, $translator);
    }

    /**
     * @param TranslatorInterface $translator
     */
    private function checkTranslateBufferWithoutTranslatedPhrase(TranslatorInterface $translator)
    {
        $bufferCaptcher = new BufferCaptcher();
        $html = '<div class="test">' . $bufferCaptcher->add('Hello') . '</div>';
        $buffer = $bufferCaptcher->getBuffer();
        $bufferContent = new BufferContent($html, $buffer);

        $bufferTranslate = new BufferTranslate();
        $translatedHtml = $bufferTranslate->translateBuffer($bufferContent, $translator);

        $this->assertEquals('<div class="test">Hello</div>', $translatedHtml);
    }

    /**
     * @param SourceInterface $source
     * @param Language $languageForTranslate
     * @param Translator $translator
     * @throws SourceException
     */
    private function checkTranslateBuffer(SourceInterface $source, Language $languageForTranslate, Translator $translator)
    {
        $source->saveTranslate($languageForTranslate->getAlias(), 'Hello', 'Привіт');

        $bufferCaptcher = new BufferCaptcher();
        $html = '<div class="test">' . $bufferCaptcher->add('Hello') . '</div>';
        $buffer = $bufferCaptcher->getBuffer();
        $bufferContent = new BufferContent($html, $buffer);

        $bufferTranslate = new BufferTranslate();
        $translatedHtml = $bufferTranslate->translateBuffer($bufferContent, $translator);

        $this->assertEquals('<div class="test">Привіт</div>', $translatedHtml);

        $source->delete('Hello');
    }

    /**
     * @param SourceInterface $source
     * @param Language $languageForTranslate
     * @param Translator $translator
     * @throws SourceException
     */
    private function checkTranslateBuffersWithProcessors(SourceInterface $source, Language $languageForTranslate, TranslatorInterface $translator)
    {
        $source->saveTranslate($languageForTranslate->getAlias(), 'Hello', 'Привіт');

        $contentProcessorsManager = new ContentProcessorsManager();
        $contentProcessorsManager->addTranslateProcessor(new CustomTagProcessor('<translate>', '</translate>', true));
        $contentProcessorsManager->addTranslateProcessor(new SimpleTextProcessor(['<']));

        $bufferCaptcher = new BufferCaptcher();
        $html = '<div class="test">';
        // SimpleTextProcessor
        $html .= $bufferCaptcher->add('Hello');
        // CustomTagProcessor
        $html .= ' - '.$bufferCaptcher->add('<translate>Hello</translate>');
        // It should not be translated
        $html .= '<div>Hello</div>';
        $html .= '</div>';
        $buffer = $bufferCaptcher->getBuffer();
        $bufferContent = new BufferContent($html, $buffer);

        $correctTranslateHtml = '<div class="test">Привіт - Привіт<div>Hello</div></div>';

        $bufferTranslate = new BufferTranslate();

        // Default buffer translate with processes
        $translatedHtml = $bufferTranslate->translateBuffersWithProcessors($bufferContent, $translator, $contentProcessorsManager);
        $this->assertEquals($translatedHtml, $correctTranslateHtml);

        $source->delete('Hello');
    }

    /**
     * @param SourceInterface $source
     * @param Language $languageForTranslate
     * @param Translator $translator
     * @throws SourceException
     */
    private function checkTranslateBuffersWithProcessorsByOneRequest(SourceInterface $source, Language $languageForTranslate, Translator $translator)
    {
        $source->saveTranslate($languageForTranslate->getAlias(), 'Hello', 'Привіт');

        $contentProcessorsManager = new ContentProcessorsManager();
        $contentProcessorsManager->addTranslateProcessor(new CustomTagProcessor('<translate>', '</translate>', true));
        $contentProcessorsManager->addTranslateProcessor(new SimpleTextProcessor(['<']));

        $bufferCaptcher = new BufferCaptcher();
        $html = '<div class="test">';
        // SimpleTextProcessor
        $html .= $bufferCaptcher->add('Hello');
        // CustomTagProcessor
        $html .= ' - '.$bufferCaptcher->add('<translate>Hello</translate>');
        // It should not be translated
        $html .= '<div>Hello</div>';
        $html .= '</div>';
        $buffer = $bufferCaptcher->getBuffer();
        $bufferContent = new BufferContent($html, $buffer);

        $correctTranslateHtml = '<div class="test">Привіт - Привіт<div>Hello</div></div>';

        $bufferTranslate = new BufferTranslate();

        // Buffer translate with one source request
        $translatedHtml = $bufferTranslate->translateBuffersWithProcessorsByOneRequest($bufferContent, $translator, $contentProcessorsManager);
        $this->assertEquals($translatedHtml, $correctTranslateHtml);

        $source->delete('Hello');
    }
}
