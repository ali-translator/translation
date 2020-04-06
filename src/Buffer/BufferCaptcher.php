<?php

namespace ALI\Translation\Buffer;

use ALI\Translation\Buffer\KeyGenerators\StaticKeyGenerator;

/**
 * BufferCaptcher
 */
class BufferCaptcher
{
    /**
     * @var Buffer
     */
    protected $buffer;

    /**
     * @param null|Buffer $buffer
     */
    public function __construct(Buffer $buffer = null)
    {
        if ($buffer) {
            $this->buffer = $buffer;
        } else {
            $keyGenerator = new StaticKeyGenerator('#--ALI:buffer:', '--#');
            $this->buffer = new Buffer($keyGenerator);
        }
    }

    /**
     * Buffering content in callback function
     * @param \Closure $callback
     */
    public function buffering(\Closure $callback)
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
     * @return Buffer
     */
    public function getBuffer(): Buffer
    {
        return $this->buffer;
    }

    /**
     * @param string $content
     * @param Buffer|null $buffer
     * @return string
     */
    public function add($content, Buffer $buffer = null)
    {
        $bufferContent = new BufferContent($content, $buffer);

        return $this->buffer->add($bufferContent);
    }
}
