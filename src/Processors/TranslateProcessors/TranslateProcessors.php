<?php

namespace ALI\Translation\Processors\TranslateProcessors;

use ALI\Translation\Translate\Translators\TranslatorInterface;

/**
 * Interface TranslateProcessors
 */
interface TranslateProcessors
{
    /**
     * @param string $content
     * @param string $cleanContent
     * @param TranslatorInterface $translator
     * @return string
     */
    public function process($content, $cleanContent, TranslatorInterface $translator);
}
