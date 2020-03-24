<?php

namespace ALI\Translation\Translate\Sources;

use ALI\Translation\Buffer\Buffer;
use ALI\Translation\Translate\Language\LanguageInterface;

/**
 * FakeBufferSource
 */
class FakeBufferSource implements SourceInterface
{
    /**
     * @var LanguageInterface
     */
    protected $originalLanguage;

    /**
     * @var Buffer
     */
    protected $buffer;

    /**
     * @param LanguageInterface $originalLanguage
     * @param Buffer $buffer
     */
    public function __construct(LanguageInterface $originalLanguage, Buffer $buffer)
    {
        $this->originalLanguage = $originalLanguage;
        $this->buffer = $buffer;
    }

    /**
     * @inheritDoc
     */
    public function getOriginalLanguage()
    {
        return $this->originalLanguage;
    }

    /**
     * @param string $phrase
     * @param LanguageInterface $language
     * @return string
     */
    public function getTranslate($phrase, LanguageInterface $language)
    {
        if ($this->originalLanguage->getAlias() === $language->getAlias()) {
            $translate = $phrase;
        } else {
            if (isset($this->temporaryTranslation[$phrase][$language->getAlias()])) {
                $translate = $this->temporaryTranslation[$phrase][$language->getAlias()];
            } else {
                $translate = $this->buffer->addContent($phrase);
            }
        }

        return $translate;
    }

    /**
     * @param array $phrases
     * @param LanguageInterface $language
     * @return array
     */
    public function getTranslates(array $phrases, LanguageInterface $language)
    {
        $translatedArray = [];
        foreach ($phrases as $phrase) {
            $translatedArray[$phrase] = $this->getTranslate($phrase, $language);
        }

        return $translatedArray;
    }

    /**
     * @var array
     */
    protected $temporaryTranslation;

    /**
     * @inheritDoc
     */
    public function saveTranslate(LanguageInterface $language, $original, $translate)
    {
        $this->temporaryTranslation[$original][$language->getAlias()] = $translate;
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
}
