<?php

namespace ALI\Translation\Translate\Language;

/**
 * LanguageInterface Interface
 */
interface LanguageInterface
{
    /**
     * Language title (Русский, English)
     * @return string
     */
    function getTitle();

    /**
     * Language alias (ru, en)
     * @return string
     */
    function getAlias();
}
