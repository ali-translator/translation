<?php

namespace ALI\Translation\Translate\PhraseDecorators\TranslateDecorators;

/**
 * Interface
 */
interface TranslatePhraseDecorator
{
    /**
     * @param string $original
     * @param string $translate
     * @return string - translate string
     */
    public function decorate($original, $translate);
}
