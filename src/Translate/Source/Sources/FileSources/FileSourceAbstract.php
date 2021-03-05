<?php

namespace ALI\Translation\Translate\Source\Sources\FileSources;

use ALI\Translation\Translate\Source\Exceptions\SourceException;
use ALI\Translation\Translate\Source\Installers\FileSourceInstaller;
use ALI\Translation\Translate\Source\Installers\SourceInstallerInterface;
use ALI\Translation\Translate\Source\SourceInterface;

/**
 * FileSourceAbstract
 */
abstract class FileSourceAbstract implements SourceInterface
{
    /**
     * @var string
     */
    protected $filesExtension;

    /**
     * @param array $phrases
     * @param string $languageAlias
     * @return array
     * @throws SourceException
     */
    public function getTranslates(array $phrases, $languageAlias)
    {
        $translatePhrases = [];
        foreach ($phrases as $phrase) {
            $translatePhrases[$phrase] = $this->getTranslate($phrase, $languageAlias);
        }

        return $translatePhrases;
    }

    /**
     * @return bool
     */
    public function isSensitiveForRequestsCount()
    {
        return false;
    }

    /**
     * @return string[]
     */
    public function getExistedTranslationLanguages()
    {
        $translateAliases = [];

        $globIterator = $this->getGlobIterator();
        while ($globIterator->valid()) {
            list(, $translateAlias) = explode('_', $globIterator->current()->getBasename('.csv'));
            $translateAliases[] = $translateAlias;
            $globIterator->next();
        }

        return $translateAliases;
    }

    public function getGlobIterator()
    {
        return new \GlobIterator($this->directoryPath . DIRECTORY_SEPARATOR . $this->getOriginalLanguageAlias() . '*.' . $this->filesExtension);
    }

    /**
     * @return SourceInstallerInterface|FileSourceInstaller
     */
    public function generateInstaller()
    {
        return new FileSourceInstaller($this->directoryPath, $this->filesExtension);
    }
}
