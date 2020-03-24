<?php

namespace ALI\Translation\Processors\PreProcessors;

/**
 * Interface PreProcessorInterface
 */
interface PreProcessorInterface
{
    /**
     * @param string $content
     * @return string
     */
    public function process($content);
}
