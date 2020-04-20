<?php

namespace ALI\Translation\Buffer;

use ALI\Translation\Buffer\KeyGenerators\KeyGenerator;
use ArrayIterator;
use IteratorAggregate;
use IteratorIterator;
use Traversable;

class BufferContentCollection implements IteratorAggregate
{
    /**
     * @var KeyGenerator
     */
    protected $keyGenerator;

    /**
     * @var BufferContent[]
     */
    protected $buffersContent = [];

    /**
     * @var int
     */
    protected $idIncrementValue = 0;

    /**
     * @param KeyGenerator $keyGenerator
     */
    public function __construct(KeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * @param string $content
     * @return string
     */
    public function addContent($content)
    {
        return $this->add(new BufferContent($content));
    }

    /**
     * Add buffer and get string buffer key
     * (after translate we replace this key two content)
     * @param BufferContent $bufferContent
     * @param string|null $bufferContentId
     * @return string
     */
    public function add(BufferContent $bufferContent, $bufferContentId = null)
    {
        $bufferContentId = $bufferContentId ?: $this->idIncrementValue++;
        $this->buffersContent[$bufferContentId] = $bufferContent;

        return $this->generateBufferKey($bufferContentId);
    }

    /**
     * @param int $bufferContentId
     * @return null|BufferContent
     */
    public function getBufferContent($bufferContentId)
    {
        return !empty($this->buffersContent[$bufferContentId]) ? $this->buffersContent[$bufferContentId] : null;
    }

    /**
     * @return BufferContent[]
     */
    public function getBuffersContent()
    {
        return $this->buffersContent;
    }

    /**
     * @param int $bufferContentId
     */
    public function remove($bufferContentId)
    {
        if (isset($this->buffersContent[$bufferContentId])) {
            unset($this->buffersContent[$bufferContentId]);
        }
    }

    /**
     * Clear buffers contents
     */
    public function clear()
    {
        $this->buffersContent = [];
    }

    /**
     * @param int $id
     * @return string
     */
    public function generateBufferKey($id)
    {
        return $this->keyGenerator->generateKey($id);
    }


    /**
     * @return IteratorIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->buffersContent);
    }
}
