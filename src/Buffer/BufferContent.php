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
    protected $withContentTranslate;

    /**
     * @param string $content
     * @param Buffer $buffer
     * @param bool $withContentTranslate
     */
    public function __construct($content, Buffer $buffer = null, $withContentTranslate = true)
    {
        $this->content = $content;
        $this->buffer = $buffer;
        $this->withContentTranslate = $withContentTranslate;
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
    public function isWithContentTranslate()
    {
        return $this->withContentTranslate;
    }
}
