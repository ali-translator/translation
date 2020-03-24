<?php

namespace ALI\Translation\Translate\OriginalProcessors;

/**
 * Class ReplaceNumbersOriginalProcessor
 * @package ALI\Translation\Processors\PreProcessors
 */
class ReplaceNumbersOriginalProcessor implements OriginalProcessorInterface
{
    /**
     * @param string $original
     * @return string
     */
    public function process($original)
    {
        return preg_replace('!\d+!', '0', $original);
    }
}
