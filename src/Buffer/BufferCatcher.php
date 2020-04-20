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
     * @var BufferCollection
     */
    protected $buffer;

    /**
     * @param null|BufferCollection $buffer
     */
    public function __construct(BufferCollection $buffer = null)
    {
        if ($buffer) {
            $this->buffer = $buffer;
        } else {
            $keyGenerator = new StaticKeyGenerator('#--ALI:buffer:', '--#');
            $this->buffer = new BufferCollection($keyGenerator);
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
     * @return BufferCollection
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * @param string                $content
     * @param BufferCollection|null $buffer
     * @return string
     */
    public function add($content, BufferCollection $buffer = null)
    {
        $bufferContent = new BufferContent($content, $buffer);

        return $this->buffer->add($bufferContent);
    }
}
