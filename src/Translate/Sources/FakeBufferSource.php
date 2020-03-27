<?php

namespace ALI\Translation\Translate\Sources;

use ALI\Translation\Buffer\Buffer;

/**
 * FakeBufferSource
 */
class FakeBufferSource implements SourceInterface
{
    /**
     * @var string
     */
    protected $originalLanguageAlias;

    /**
     * @var Buffer
     */
    protected $buffer;

    /**
     * @param string $originalLanguageAlias
     * @param Buffer $buffer
     */
    public function __construct($originalLanguageAlias, Buffer $buffer)
    {
        $this->originalLanguageAlias = $originalLanguageAlias;
        $this->buffer = $buffer;
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
    protected $temporaryTranslation;

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
}
