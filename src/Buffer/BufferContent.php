<?php

namespace ALI\Translation\Buffer;

/**
 * BufferContent
 */
class BufferContent
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var null|BufferCollection
     */
    protected $bufferCollection;

    /**
     * @var bool
     */
    protected $withContentTranslation;

    /**
     * @param string           $content
     * @param BufferCollection $bufferCollection
     * @param bool             $withContentTranslation
     */
    public function __construct($content, BufferCollection $bufferCollection = null, $withContentTranslation = true)
    {
        $this->content = $content;
        $this->bufferCollection = $bufferCollection;
        $this->withContentTranslation = $withContentTranslation;
    }

    /**
     * @return string
     */
    public function getContentString()
    {
        return $this->content;
    }

    /**
     * @return null|BufferCollection
     */
    public function getBufferCollection()
    {
        return $this->bufferCollection;
    }

    /**
     * @return bool
     */
    public function withContentTranslation()
    {
        return $this->withContentTranslation;
    }
}
