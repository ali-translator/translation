<?php

namespace ALI\Translation\Translate\Language;

/**
 * Language
 */
class Language implements LanguageInterface
{
    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $title;

    /**
     * @param string $alias
     * @param string $title
     */
    public function __construct($alias, $title = '')
    {
        $this->alias = $alias;
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAlias();
    }
}
