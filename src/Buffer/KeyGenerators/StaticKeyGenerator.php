<?php

namespace ALI\Translation\Buffer\KeyGenerators;

/**
 * StaticKeyGenerator
 */
class StaticKeyGenerator implements KeyGenerator
{
    /**
     * @var string
     */
    protected $keyPrefix;

    /**
     * @var string
     */
    protected $keyPostfix;

    /**
     * @param string $keyPrefix
     * @param string $keyPostfix
     */
    public function __construct($keyPrefix, $keyPostfix)
    {
        $this->keyPrefix = $keyPrefix;
        $this->keyPostfix = $keyPostfix;
    }

    /**
     * @param string $contentId
     * @return string
     */
    public function generateKey($contentId)
    {
        return $this->keyPrefix . $contentId . $this->keyPostfix;
    }
}
