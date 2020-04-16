<?php

namespace ALI\Translation\Helpers\QuickStart;

use ALI\Translation\ALIAbc;
use ALI\Translation\ContentProcessors\PreTranslateProcessors\HtmlCommentPreProcessor;
use ALI\Translation\ContentProcessors\PreTranslateProcessors\IgnoreHtmlTagsPreProcessor;
use ALI\Translation\ContentProcessors\PreTranslateProcessors\SliIgnoreTagPreProcessor;
use ALI\Translation\ContentProcessors\ContentProcessorsManager;
use ALI\Translation\ContentProcessors\TranslateProcessors\HtmlAttributesProcessor;
use ALI\Translation\ContentProcessors\TranslateProcessors\HtmlTagProcessor;
use ALI\Translation\ContentProcessors\TranslateProcessors\SimpleTextProcessor;
use ALI\Translation\Translate\PhraseDecorators\OriginalDecorators\ReplaceNumbersOriginalDecorator;
use ALI\Translation\Translate\PhraseDecorators\OriginalPhraseDecoratorManager;
use ALI\Translation\Translate\PhraseDecorators\TranslateDecorators\ReplaceNumbersTranslateDecorator;
use ALI\Translation\Translate\PhraseDecorators\TranslatePhraseDecoratorManager;
use ALI\Translation\Translate\Sources\CsvFileSource;
use ALI\Translation\Translate\Sources\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translation\Translate\Sources\Installers\MySqlSourceInstaller;
use ALI\Translation\Translate\Sources\MySqlSource;
use ALI\Translation\Translate\Translators\DecoratedTranslator;
use ALI\Translation\Translate\Translators\Translator;
use ALI\Translation\Translate\Translators\TranslatorInterface;
use PDO;

/**
 * Class
 */
class ALIAbcFactory
{
    /**
     * @param PDO $connection
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return ALIAbc
     */
    public function createALIByHtmlBufferMysqlSource(PDO $connection, $originalLanguageAlias, $currentLanguageAlias)
    {
        $translator = $this->generateMysqlTranslator($connection, $originalLanguageAlias, $currentLanguageAlias);

        $processorsManager = $this->generateBaseHtmlProcessorManager();

        return new ALIAbc($translator, $processorsManager);
    }

    /**
     * @param PDO $connection
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return ALIAbc
     */
    public function createALIByMysqlSource(PDO $connection, $originalLanguageAlias, $currentLanguageAlias)
    {
        $translator = $this->generateMysqlTranslator($connection, $originalLanguageAlias, $currentLanguageAlias);

        return new ALIAbc($translator);
    }

    /**
     * @param $translationDirectoryPath
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return ALIAbc
     * @throws UnsupportedLanguageAliasException
     */
    public function createALIByHtmlBufferCsvSource($translationDirectoryPath, $originalLanguageAlias, $currentLanguageAlias)
    {
        $translator = $this->generateCsvTranslator($translationDirectoryPath, $originalLanguageAlias, $currentLanguageAlias);

        $processorsManager = $this->generateBaseHtmlProcessorManager();

        return new ALIAbc($translator, $processorsManager);
    }

    /**
     * @param $translationDirectoryPath
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return ALIAbc
     * @throws UnsupportedLanguageAliasException
     */
    public function createALIByCsvSource($translationDirectoryPath, $originalLanguageAlias, $currentLanguageAlias)
    {
        $translator = $this->generateCsvTranslator($translationDirectoryPath, $originalLanguageAlias, $currentLanguageAlias);

        return new ALIAbc($translator);
    }

    /**
     * @param PDO $connection
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return TranslatorInterface
     */
    private function generateMysqlTranslator(PDO $connection, $originalLanguageAlias, $currentLanguageAlias)
    {
        $source = new MySqlSource($connection, $originalLanguageAlias);
        $sourceInstaller = new MySqlSourceInstaller($connection);
        if (!$sourceInstaller->isInstalled()) {
            $sourceInstaller->install();
        }

        $baseTranslator = new Translator($currentLanguageAlias, $source);

        return $decoratedTranslator = $this->generateBaseDecoratedTranslator($baseTranslator);
    }

    /**
     * @return ContentProcessorsManager
     */
    private function generateBaseHtmlProcessorManager()
    {
        $contentProcessorsManager = new ContentProcessorsManager();

        $contentProcessorsManager->addPreProcessor(new HtmlCommentPreProcessor());
        $contentProcessorsManager->addPreProcessor(new IgnoreHtmlTagsPreProcessor());
        $contentProcessorsManager->addPreProcessor(new SliIgnoreTagPreProcessor());

        $contentProcessorsManager->addTranslateProcessor(new HtmlTagProcessor());
        $contentProcessorsManager->addTranslateProcessor(new HtmlAttributesProcessor());
        $contentProcessorsManager->addTranslateProcessor(new SimpleTextProcessor());

        return $contentProcessorsManager;
    }

    /**
     * @param $translationDirectoryPath
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return TranslatorInterface
     * @throws UnsupportedLanguageAliasException
     */
    private function generateCsvTranslator($translationDirectoryPath, $originalLanguageAlias, $currentLanguageAlias)
    {
        $source = new CsvFileSource($translationDirectoryPath, $originalLanguageAlias);
        $fileCsvPath = $source->getLanguageFilePath($currentLanguageAlias);
        if (!file_exists($fileCsvPath)) {
            touch($fileCsvPath);
        }

        $baseTranslator = new Translator($currentLanguageAlias, $source);

        return $decoratedTranslator = $this->generateBaseDecoratedTranslator($baseTranslator);
    }

    /**
     * @param TranslatorInterface $translator
     * @return TranslatorInterface
     */
    private function generateBaseDecoratedTranslator(TranslatorInterface $translator)
    {
        $originalDecoratorManger = new OriginalPhraseDecoratorManager([
            new ReplaceNumbersOriginalDecorator(),
        ]);
        $translateDecoratorManager = new TranslatePhraseDecoratorManager([
            new ReplaceNumbersTranslateDecorator(),
        ]);

        return new DecoratedTranslator($translator, $originalDecoratorManger, $translateDecoratorManager);
    }
}
