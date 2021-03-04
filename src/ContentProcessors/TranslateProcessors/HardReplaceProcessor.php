<?php

namespace ALI\Translation\ContentProcessors\TranslateProcessors;

use ALI\Translation\Translate\Translators\PlainTranslatorInterface;

/**
 * This processor only replace all occurrences.
 * For example: you may replace image url from /logo.png to /ru_logo.png
 * Case sensitive search!
 * Class HardReplaceProcessor
 * @package ALI\Translation\Processors\TranslateProcessors
 */
class HardReplaceProcessor implements TranslateProcessors
{
    protected $replacements = [];

    /**
     * @return array
     */
    public function getReplacements()
    {
        return $this->replacements;
    }

    /**
     * @param array $replacements
     * @return $this
     */
    public function setReplacements(array $replacements)
    {
        $this->replacements = $replacements;

        return $this;
    }

    /**
     * @param $search
     * @param $replace
     * @return $this
     */
    public function addReplacement($search, $replace)
    {
        $this->replacements[$search] = $replace;

        return $this;
    }

    /**
     * @param string $content
     * @param string $cleanContent
     * @param PlainTranslatorInterface $translator
     * @return string
     */
    public function process($content, $cleanContent, PlainTranslatorInterface $translator)
    {
        return strtr($content, $this->getReplacements());
    }
}
