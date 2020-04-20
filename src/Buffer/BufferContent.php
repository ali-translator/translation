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
    protected $childContentCollection;

    /**
     * @var bool
     */
    protected $withContentTranslation;

    /**
     * @param string                  $content
     * @param BufferContentCollection $childContentCollection - this is for nested buffers (buffers inside buffer)
     * @param bool                    $withContentTranslation
     */
    public function __construct($content, BufferContentCollection $childContentCollection = null, $withContentTranslation = true)
    {
        $this->content = $content;
        $this->childContentCollection = $childContentCollection;
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
    public function getChildContentCollection()
    {
        return $this->childContentCollection;
    }

    /**
     * @return bool
     */
    public function withContentTranslation()
    {
        return $this->withContentTranslation;
    }
}
