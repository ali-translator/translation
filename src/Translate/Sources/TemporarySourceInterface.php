<?php

namespace ALI\Translation\Translate\Sources;

use ALI\Translation\Buffer\BufferContentCollection;
use ALI\Translation\Translate\Sources\Installers\SourceInstallerInterface;

/**
 * TemporarySource
 */
class TemporarySourceInterface implements SourceInterface, SourceInstallerInterface
{
    /**
     * @var string
     */
    protected $originalLanguageAlias;

    /**
     * @var BufferContentCollection
     */
    protected $buffer;

    /**
     * @param string $originalLanguageAlias
     * @param BufferContentCollection $buffer
     */
    public function __construct($originalLanguageAlias, BufferContentCollection $buffer)
    {
        $this->originalLanguageAlias = $originalLanguageAlias;
        $this->buffer = $buffer;
    }

    /**
     * @return bool
     */
    public function isSensitiveForRequestsCount()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getOriginalLanguageAlias()
    {
        return $this->originalLanguageAlias;
    }

    /**
     * @param string $phrase
     * @param string $languageAlias
     * @return string
     */
    public function getTranslate($phrase, $languageAlias)
    {
        if ($this->originalLanguageAlias === $languageAlias) {
            $translate = $phrase;
        } else {
            if (isset($this->temporaryTranslation[$phrase][$languageAlias])) {
                $translate = $this->temporaryTranslation[$phrase][$languageAlias];
            } else {
                $translate = $this->buffer->addContent($phrase);
            }
        }

        return $translate;
    }

    /**
     * @param array $phrases
     * @param string $languageAlias
     * @return array
     */
    public function getTranslates(array $phrases, $languageAlias)
    {
        $translatedArray = [];
        foreach ($phrases as $phrase) {
            $translatedArray[$phrase] = $this->getTranslate($phrase, $languageAlias);
        }

        return $translatedArray;
    }

    /**
     * @var array
     */
    protected $temporaryTranslation = [];

    /**
     * @param string $languageAlias
     * @param string $original
     * @param string $translate
     */
    public function saveTranslate($languageAlias, $original, $translate)
    {
        $this->temporaryTranslation[$original][$languageAlias] = $translate;
    }

    /**
     * @inheritDoc
     */
    public function delete($original)
    {
        if (isset($this->temporaryTranslation[$original])) {
            unset($this->temporaryTranslation[$original]);
        }
    }

    /**
     * @inheritDoc
     */
    public function saveOriginals(array $phrases)
    {
        foreach ($phrases as $phrase) {
            if (isset($this->temporaryTranslation[$phrase])) {
                continue;
            }
            $this->temporaryTranslation[$phrase] = [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getExistOriginals(array $phrases)
    {
        $existPhrases = [];
        foreach ($phrases as $phrase) {
            if (isset($this->temporaryTranslation[$phrase])) {
                $existPhrases[] = $phrase;
            }
        }

        return $existPhrases;
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return true;
    }

    /**
     * Install
     */
    public function install()
    {
        return;
    }

    /**
     * Destroy
     */
    public function destroy()
    {
        $this->temporaryTranslation = [];
    }

    /**
     * @return $this|SourceInstallerInterface
     */
    public function generateInstaller()
    {
        return $this;
    }
}
