<?php

namespace ALI\Translation\Buffer;

use ALI\Translation\Buffer\KeyGenerators\StaticKeyGenerator;
use Closure;

/**
 * BufferCatcher
 */
class BufferCatcher
{
    /**
     * @var BufferContentCollection
     */
    protected $buffer;

    /**
     * @param null|BufferContentCollection $buffer
     */
    public function __construct(BufferContentCollection $buffer = null)
    {
        if ($buffer) {
            $this->buffer = $buffer;
        } else {
            $keyGenerator = new StaticKeyGenerator('#--ALI:buffer:', '--#');
            $this->buffer = new BufferContentCollection($keyGenerator);
        }
    }

    /**
     * Buffering content in callback function
     * @param Closure $callback
     */
    public function buffering(Closure $callback)
    {
        $this->start();
        $callback();
        $this->end();
    }

    /**
     * Start buffering
     */
    public function start()
    {
        ob_start(function ($bufferContent) {
            return $this->buffer->add(new BufferContent($bufferContent));
        });
    }

    /**
     * Stop buffering and get stub content
     */
    public function end()
    {
        ob_end_flush();
    }

    /**
     * @return BufferContentCollection
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * @param string                       $content
     * @param BufferContentCollection|null $buffer
     * @return string
     */
    public function add($content, BufferContentCollection $buffer = null)
    {
        $bufferContent = new BufferContent($content, $buffer);

        return $this->buffer->add($bufferContent);
    }
}
