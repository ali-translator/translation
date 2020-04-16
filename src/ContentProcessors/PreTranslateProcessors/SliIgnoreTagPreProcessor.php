<?php

namespace ALI\Translation\ContentProcessors\PreTranslateProcessors;

/**
 * Class SliIgnoreTagPreProcessor
 * @package ALI\Translation\Processors
 */
class SliIgnoreTagPreProcessor extends PreProcessorAbstract
{
    const HTML_VAR_PATTERN = '<!--SLI::%s-->';

    /**
     * @param string $content
     * @return string
     */
    public function process($content)
    {
        return preg_replace('#(' . $this->ignoreStart() . '.*' . $this->ignoreEnd() . ')#Usi', '', $content);
    }

    /**
     * @return string
     * @internal param bool $print
     */
    public function ignoreStart()
    {
        return sprintf(self::HTML_VAR_PATTERN, 'ignore');
    }

    /**
     * @return string
     */
    public function ignoreEnd()
    {
        return sprintf(self::HTML_VAR_PATTERN, 'endIgnore');
    }
}
