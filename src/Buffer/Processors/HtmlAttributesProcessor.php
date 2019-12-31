<?php

namespace ALI\Buffer\Processors;

/**
 * Class HtmlAttributesProcessor
 * @package ALI\Buffer\Processors
 */
class HtmlAttributesProcessor extends AbstractHtmlProcessor
{
    protected $allowAttributes = [];

    /**
     * HtmlAttributesProcessor constructor.
     * @param array $allowAttributes
     */
    public function __construct(array $allowAttributes)
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

        //test regex https://regex101.com/r/aOX8Fo/4
        return '$
          (?:<[^>]+\s+(?:' . implode('|', $regexp) . ')\s*=\s*("|\'))   #Attributes in tag
                (?:(?:&\#?[a-z0-9]{1,7};))*                                  #Html entities and untranslated symbols 
            		(?<original>[\w][^<>]*)                                  #Translate content
                (?:(?:&\#?[a-z0-9]{1,7};)|\s)*                               #Html entities and spaces
  		  (?:(?!\\\)\\1)                                                     #Close attribute quote
        $Uuxsi';
    }
}