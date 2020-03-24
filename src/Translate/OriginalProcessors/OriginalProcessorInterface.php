<?php

namespace ALI\Translation\Translate\OriginalProcessors;

/**
 * Interface OriginalProcessorInterface
 * @package ALI\Translation\Translate\OriginalProcessors
 */
interface OriginalProcessorInterface
{
    /**
     * @param string $original
     * @return string
     */
    public function process($original);
}
