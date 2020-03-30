<?php

namespace ALI\Translation\Languages;

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
