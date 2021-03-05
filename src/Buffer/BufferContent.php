<?php

namespace ALI\Translation\Buffer;

/**
 * BufferContent
 */
class BufferContent
{
    const OPTION_FORMAT = 1;
    const OPTION_WITH_CONTENT_TRANSLATION = 2;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var null|BufferContentCollection
     */
    protected $childContentCollection;

    /**
     * @var array
     */
    protected $options = [
        self::OPTION_FORMAT => 'string',
        // TODO change to "false"?
        self::OPTION_WITH_CONTENT_TRANSLATION => true,
    ];

    /**
     * @param string $content
     * @param BufferContentCollection $childContentCollection - this is for nested buffers (buffers inside buffer)
     * @param array $options
     * @param bool $withContentTranslation
     */
    public function __construct($content, BufferContentCollection $childContentCollection = null, $options = [])
    {
        $this->content = $content;
        $this->childContentCollection = $childContentCollection;
        $this->options = $options + $this->options;
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
        return $this->options[self::OPTION_WITH_CONTENT_TRANSLATION];
    }
}
