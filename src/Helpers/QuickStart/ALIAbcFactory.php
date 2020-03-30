<?php

namespace ALI\Translation\Helpers\QuickStart;

use ALI\Translation\ALIAbc;
use ALI\Translation\Processors\PreProcessors\HtmlCommentPreProcessor;
use ALI\Translation\Processors\PreProcessors\IgnoreHtmlTagsPreProcessor;
use ALI\Translation\Processors\PreProcessors\SliIgnoreTagPreProcessor;
use ALI\Translation\Processors\ProcessorsManager;
use ALI\Translation\Processors\TranslateProcessors\HtmlAttributesProcessor;
use ALI\Translation\Processors\TranslateProcessors\HtmlTagProcessor;
use ALI\Translation\Processors\TranslateProcessors\SimpleTextProcessor;
use ALI\Translation\Translate\Sources\CsvFileSource;
use ALI\Translation\Translate\Sources\Exceptions\CsvFileSource\UnsupportedLanguageAliasException;
use ALI\Translation\Translate\Sources\Installers\MySqlSourceInstaller;
use ALI\Translation\Translate\Sources\MySqlSource;
use ALI\Translation\Translate\Translators\Translator;
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
     * @return Translator
     */
    private function generateMysqlTranslator(PDO $connection, $originalLanguageAlias, $currentLanguageAlias)
    {
        $source = new MySqlSource($connection, $originalLanguageAlias);
        $sourceInstaller = new MySqlSourceInstaller($connection);
        if (!$sourceInstaller->isInstalled()) {
            $sourceInstaller->install();
        }

        return new Translator($currentLanguageAlias, $source);
    }

    /**
     * @return ProcessorsManager
     */
    private function generateBaseHtmlProcessorManager()
    {
        $processorsManager = new ProcessorsManager();

        $processorsManager->addPreProcessor(new HtmlCommentPreProcessor());
        $processorsManager->addPreProcessor(new IgnoreHtmlTagsPreProcessor());
        $processorsManager->addPreProcessor(new SliIgnoreTagPreProcessor());

        $processorsManager->addTranslateProcessor(new HtmlTagProcessor());
        $processorsManager->addTranslateProcessor(new HtmlAttributesProcessor());
        $processorsManager->addTranslateProcessor(new SimpleTextProcessor());

        return $processorsManager;
    }

    /**
     * @param $translationDirectoryPath
     * @param $originalLanguageAlias
     * @param $currentLanguageAlias
     * @return Translator
     * @throws UnsupportedLanguageAliasException
     */
    private function generateCsvTranslator($translationDirectoryPath, $originalLanguageAlias, $currentLanguageAlias)
    {
        $source = new CsvFileSource($translationDirectoryPath, $originalLanguageAlias);
        $fileCsvPath = $source->getLanguageFilePath($currentLanguageAlias);
        if (!file_exists($fileCsvPath)) {
            touch($fileCsvPath);
        }

        return new Translator($currentLanguageAlias, $source);
    }
}
