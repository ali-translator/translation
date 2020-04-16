<?php

namespace ALI\Translation\ContentProcessors\PreTranslateProcessors;

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
