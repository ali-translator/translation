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
     * @var null|BufferContentCollection
     */
    protected $bufferContentCollection;

    /**
     * @var bool
     */
    protected $withContentTranslation;

    /**
     * @param string                  $content
     * @param BufferContentCollection $bufferContentCollection
     * @param bool                    $withContentTranslation
     */
    public function __construct($content, BufferContentCollection $bufferContentCollection = null, $withContentTranslation = true)
    {
        $this->content = $content;
        $this->bufferContentCollection = $bufferContentCollection;
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
     * @return null|BufferContentCollection
     */
    public function getBufferContentCollection()
    {
        return $this->bufferContentCollection;
    }

    /**
     * @return bool
     */
    public function withContentTranslation()
    {
        return $this->withContentTranslation;
    }
}
