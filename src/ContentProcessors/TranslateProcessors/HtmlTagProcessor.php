<?php

namespace ALI\Translation\ContentProcessors\TranslateProcessors;

/**
 * Class HtmlTagProcessor
 * @package ALI\Translation\Processors\TranslateProcessors
 */
class HtmlTagProcessor extends AbstractHtmlProcessor
{
    /**
     * Get RegEx for parse HTML and get all phrases for translate
     * @return string
     */
    public function getFindPhrasesRegex()
    {
        //test regex https://regex101.com/r/aOX8Fo/7
        return '$
          (?:>|\A)                                      #Close tag symbol or start of string
                \s*
                (?<original>[^\s<>]*[\w][^<>]+)         #Translate phrase
                \s*
  		  (?:<|\Z)                                      #Open tag symbol or end of string
        $Uxusi';
    }
}
