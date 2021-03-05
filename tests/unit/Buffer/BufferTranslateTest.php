<?php

namespace ALI\Translation\Tests\unit\Buffer;

use ALI\Translation\Buffer\BufferCatcher;
use ALI\Translation\Buffer\BufferContent;
use ALI\Translation\Buffer\BufferTranslate;
use ALI\Translation\ContentProcessors\ContentProcessorsManager;
use ALI\Translation\ContentProcessors\TranslateProcessors\CustomTagProcessor;
use ALI\Translation\ContentProcessors\TranslateProcessors\SimpleTextProcessor;
use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Languages\Language;
use ALI\Translation\Translate\Source\Exceptions\SourceException;
use ALI\Translation\Translate\Source\SourceInterface;
use ALI\Translation\Translate\PlainTranslator\PlainTranslator;
use ALI\Translation\Translate\PlainTranslator\PlainTranslatorFactory;
use ALI\Translation\Translate\PlainTranslator\PlainTranslatorInterface;
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
        foreach ($sourceFactory->iterateAllSources($originalLanguage->getAlias()) as $source) {
            $translator = (new PlainTranslatorFactory())->createPlainTranslator($source, $currentLanguage->getAlias());

            $this->checkTranslateBufferWithoutTranslatedPhrase($translator);

            $this->checkTranslateBuffer($source, $currentLanguage, $translator);

            $this->checkTranslateBuffersWithProcessors($source, $currentLanguage, $translator);

            $this->checkTranslateBuffersWithProcessorsByOneRequest($source, $currentLanguage, $translator);
        }
    }

    /**
     * @param PlainTranslatorInterface $translator
     */
    private function checkTranslateBufferWithoutTranslatedPhrase(PlainTranslatorInterface $translator)
    {
        $bufferCatcher = new BufferCatcher();
        $html = '<div class="test">' . $bufferCatcher->add('Hello') . '</div>';
        $buffer = $bufferCatcher->getBufferContentCollection();
        $bufferContent = new BufferContent($html, $buffer);

        $bufferTranslate = new BufferTranslate();
        $translatedHtml = $bufferTranslate->translateChildContentCollection($bufferContent, $translator);

        $this->assertEquals('<div class="test">Hello</div>', $translatedHtml);
    }

    /**
     * @param SourceInterface $source
     * @param Language $languageForTranslate
     * @param PlainTranslator $translator
     * @throws SourceException
     */
    private function checkTranslateBuffer(SourceInterface $source, Language $languageForTranslate, PlainTranslator $translator)
    {
        $source->saveTranslate($languageForTranslate->getAlias(), 'Hello', 'Привіт');

        $bufferCatcher = new BufferCatcher();
        $html = '<div class="test">' . $bufferCatcher->add('Hello') . '</div>';
        $buffer = $bufferCatcher->getBufferContentCollection();
        $bufferContent = new BufferContent($html, $buffer);

        $bufferTranslate = new BufferTranslate();
        $translatedHtml = $bufferTranslate->translateChildContentCollection($bufferContent, $translator);

        $this->assertEquals('<div class="test">Привіт</div>', $translatedHtml);

        $source->delete('Hello');
    }

    /**
     * @param SourceInterface $source
     * @param Language $languageForTranslate
     * @param PlainTranslator $translator
     * @throws SourceException
     */
    private function checkTranslateBuffersWithProcessors(SourceInterface $source, Language $languageForTranslate, PlainTranslatorInterface $translator)
    {
        $source->saveTranslate($languageForTranslate->getAlias(), 'Hello', 'Привіт');

        $contentProcessorsManager = new ContentProcessorsManager();
        $contentProcessorsManager->addTranslateProcessor(new CustomTagProcessor('<translate>', '</translate>', true));
        $contentProcessorsManager->addTranslateProcessor(new SimpleTextProcessor(['<']));

        $bufferCatcher = new BufferCatcher();
        $html = '<div class="test">';
        // SimpleTextProcessor
        $html .= $bufferCatcher->add('Hello');
        // CustomTagProcessor
        $html .= ' - ' . $bufferCatcher->add('<translate>Hello</translate>');
        // It should not be translated
        $html .= '<div>Hello</div>';
        $html .= '</div>';
        $buffer = $bufferCatcher->getBufferContentCollection();
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
     * @param PlainTranslator $translator
     * @throws SourceException
     */
    private function checkTranslateBuffersWithProcessorsByOneRequest(SourceInterface $source, Language $languageForTranslate, PlainTranslator $translator)
    {
        $source->saveTranslate($languageForTranslate->getAlias(), 'Hello', 'Привіт');

        $contentProcessorsManager = new ContentProcessorsManager();
        $contentProcessorsManager->addTranslateProcessor(new CustomTagProcessor('<translate>', '</translate>', true));
        $contentProcessorsManager->addTranslateProcessor(new SimpleTextProcessor(['<']));

        $bufferCatcher = new BufferCatcher();
        $html = '<div class="test">';
        // SimpleTextProcessor
        $html .= $bufferCatcher->add('Hello');
        // CustomTagProcessor
        $html .= ' - ' . $bufferCatcher->add('<translate>Hello</translate>');
        // It should not be translated
        $html .= '<div>Hello</div>';
        $html .= '</div>';
        $buffer = $bufferCatcher->getBufferContentCollection();
        $bufferContent = new BufferContent($html, $buffer);

        $correctTranslateHtml = '<div class="test">Привіт - Привіт<div>Hello</div></div>';

        $bufferTranslate = new BufferTranslate();

        // Buffer translate with one source request
        $translatedHtml = $bufferTranslate->translateBuffersWithProcessorsByOneRequest($bufferContent, $translator, $contentProcessorsManager);
        $this->assertEquals($translatedHtml, $correctTranslateHtml);

        $source->delete('Hello');
    }
}
