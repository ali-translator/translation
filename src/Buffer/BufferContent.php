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
     * @param string $content
     * @param Buffer $buffer
     */
    public function __construct($content, Buffer $buffer = null)
    {
        $this->content = $content;
        $this->buffer = $buffer;
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
}
