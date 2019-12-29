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
     * Allow html attributes translation
     * @return array
     */
    public function getAllowAttributes()
    {
        return $this->allowAttributes;
    }

    /**
     * @param array $allowAttributes
     */
    public function setAllowAttributes($allowAttributes)
    {
        $this->allowAttributes = $allowAttributes;
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

        //test regex https://regex101.com/r/aOX8Fo/3
        return '$
          (?:<[^>]+\s+(?:' . implode('|', $regexp) . ')\s*=\s*("|\'))   #Attributes in tag
                (?:(?:&\#?[a-z0-9]{1,7};)|[^\w<])*                           #Html entities and untranslated symbols 
            		(?<original>[\w][^<>]+)                                  #Translate content
                (?:(?:&\#?[a-z0-9]{1,7};)|\s)*                               #Html entities and spaces
  		  (?:(?!\\\)\\1)                                                     #Close attribute quote
        $Uuxsi';
    }
}