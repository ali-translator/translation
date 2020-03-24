<?php

namespace ALI\Translation\Processors\PreProcessors;

/**
 * Class HtmlCommentPreProcessor
 * @package ALI\Translation\Processors
 */
class HtmlCommentPreProcessor extends PreProcessorAbstract
{
    /**
     * @param string $content
     * @return string
     */
    public function process($content)
    {
        return preg_replace('#(<!--.*-->)#Us', '', $content);
    }
}
