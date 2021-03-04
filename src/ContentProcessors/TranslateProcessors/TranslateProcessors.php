<?php

namespace ALI\Translation\ContentProcessors\TranslateProcessors;

use ALI\Translation\Translate\Translators\PlainTranslatorInterface;

/**
 * Interface TranslateProcessors
 */
interface TranslateProcessors
{
    /**
     * @param string $content
     * @param string $cleanContent
     * @param PlainTranslatorInterface $translator
     * @return string
     */
    public function process($content, $cleanContent, PlainTranslatorInterface $translator);
}
