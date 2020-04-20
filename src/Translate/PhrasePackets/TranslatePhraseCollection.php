<?php

namespace ALI\Translation\Translate\PhrasePackets;

use ArrayIterator;
use IteratorAggregate;

/**
 * TranslatePhrasePacket
 */
class TranslatePhraseCollection implements IteratorAggregate
{
    /**
     * @var string[]
     */
    private $originalsWithTranslate;

    /**
     * @param string[] $originalsWithTranslate
     */
    public function __construct(array $originalsWithTranslate = [])
    {
        $this->originalsWithTranslate = $originalsWithTranslate;
    }

    /**
     * @param string $original
     * @param string $translate
     */
    public function addTranslate($original, $translate)
    {
        $this->originalsWithTranslate[$original] = $translate;
    }

    /**
     * @param $original
     * @return string|null
     */
    public function getTranslate($original)
    {
        if (!isset($this->originalsWithTranslate[$original])) {
            return null;
        }

        return $this->originalsWithTranslate[$original];
    }

    /**
     * @param string $original
     * @return bool
     */
    public function existOriginal($original)
    {
        return isset($this->originalsWithTranslate[$original]);
    }

    /**
     * @param string $original
     * @return bool
     */
    public function existTranslate($original)
    {
        return !empty($this->originalsWithTranslate[$original]);
    }

    /**
     * @return string[]
     */
    public function getAll()
    {
        return $this->originalsWithTranslate;
    }

    /**
     * @return OriginalPhraseCollection
     */
    public function generateOriginalPhraseCollection()
    {
        $allTranslatesPhrases = $this->getAll();
        $originalPhrases = array_values($allTranslatesPhrases);

        return new OriginalPhraseCollection($originalPhrases);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->originalsWithTranslate);
    }
}
