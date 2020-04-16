<?php

namespace ALI\Translation\Translate\PhraseDecorators\OriginalDecorators;

/**
 * Interface
 */
interface OriginalPhraseDecoratorInterface
{
    /**
     * @param string $original
     * @return string
     */
    public function decorate($original);
}
