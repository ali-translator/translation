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
     * @var null|Buffer
     */
    protected $buffer;

    /**
     * @var bool
     */
    protected $withContentTranslation;

    /**
     * @param string $content
     * @param Buffer $buffer
     * @param bool $withContentTranslation
     */
    public function __construct($content, Buffer $buffer = null, $withContentTranslation = true)
    {
        $this->content = $content;
        $this->buffer = $buffer;
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
     * @return null|Buffer
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * @return bool
     */
    public function withContentTranslation()
    {
        return $this->withContentTranslation;
    }
}
