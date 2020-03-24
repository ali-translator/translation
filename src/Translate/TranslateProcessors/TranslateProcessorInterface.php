<?php

namespace ALI\Translation\Translate\TranslateProcessors;

/**
 * Interface TranslateProcessorInterface
 * @package ALI\Translation\Translate\TranslateProcessors
 */
interface TranslateProcessorInterface
{
    /**
     * @param string $original
     * @param string $translate
     * @return string - translate string
     */
    public function process($original, $translate);
}
