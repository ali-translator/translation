<?php

namespace ALI\Translation\Translate\PhrasePackets;

/**
 * OriginalPhrasePacket
 */
class OriginalPhrasePacket
{
    /**
     * @var string[]
     */
    private $originals;

    /**
     * @param string[] $originals
     */
    public function __construct(array $originals = [])
    {
        $this->originals = $originals;
    }

    /**
     * @param string $content
     */
    public function add($content)
    {
        $this->originals[$content] = $content;
    }

    /**
     * @param string $content
     * @return bool
     */
    public function exist($content)
    {
        return isset($this->originals[$content]);
    }

    /**
     * @param $content
     */
    public function remove($content)
    {
        if (isset($this->originals[$content])) {
            unset($this->originals[$content]);
        }
    }

    /**
     * @return string[]
     */
    public function getAll()
    {
        return $this->originals;
    }
}
