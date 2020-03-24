<?php

namespace ALI\Translation\Processors\TranslateProcessors;

/**
 * Class HtmlAttributesProcessor
 * @package ALI\Translation\Processors\TranslateProcessors
 */
class HtmlAttributesProcessor extends AbstractHtmlProcessor
{
    /**
     * @var array
     */
    protected $allowAttributes = [];

    /**
     * HtmlAttributesProcessor constructor.
     * @param array $allowAttributes
     */
    public function __construct(array $allowAttributes = ['title', 'alt', 'placeholder', 'content'])
    {
        $this->allowAttributes = $allowAttributes;
    }

    /**
     * Allow html attributes translation
     * @return array
     */
    public function getAllowAttributes()
    {
        return $this->allowAttributes;
    }

    /**
     * Get RegEx for parse HTML and get all phrases for translate
     * @return string
     */
    public function getFindPhrasesRegex()
    {
        $allowAttributes = $this->getAllowAttributes();
        $regexp = [];

        foreach ($allowAttributes as $attr) {
            $attr = preg_quote($attr);
            $regexp[] = '(?:' . $attr . ')';
        }

        //test regex https://regex101.com/r/aOX8Fo/6
        return '$
          (?:<[^>]+\s+(?:' . implode('|', $regexp) . ')\s*=\s*("|\'))   #Attributes in tag
                \s*
            		(?<original>[^<>]*)                                      #Translate content
                \s*
  		  (?:(?!\\\)\\1)                                                     #Close attribute quote
        $Uuxsi';
    }
}
