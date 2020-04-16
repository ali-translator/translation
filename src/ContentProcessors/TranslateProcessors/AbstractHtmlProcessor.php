<?php

namespace ALI\Translation\ContentProcessors\TranslateProcessors;

use ALI\Translation\Translate\Translators\TranslatorInterface;

/**
 * Class AbstractHtmlProcessor
 * @package ALI\Translation\Processors\TranslateProcessors
 */
abstract class AbstractHtmlProcessor implements TranslateProcessors
{
    /**
     * @param string $content
     * @param string $cleanContent
     * @param TranslatorInterface $translate
     * @return string
     * @throws \ALI\Translation\Exceptions\ALIException
     */
    public function process($content, $cleanContent, TranslatorInterface $translate)
    {
        if ($translate->isCurrentLanguageOriginal()) {
            return $content;
        }

        preg_match_all($this->getFindPhrasesRegex(), $cleanContent, $match);
        $originalData = [
            'match'    => $match[0],
            'original' => $match['original'],
        ];

        $pos = 0;
        $translatePhrasePacket = $translate->translateAll($originalData['original']);

        foreach ($originalData['original'] AS $k => $original) {

            //find original phrase position
            $pos = strpos($content, $originalData['match'][$k], $pos);
            preg_match($this->getFindPhrasesRegex(), $content, $matchPosition, PREG_OFFSET_CAPTURE, $pos);
            $pos = $matchPosition['original'][1];

            //don't replace if we don't have translation
            if (!$translatePhrasePacket->existTranslate($original)) {
                continue;
            }

            $translate = $translatePhrasePacket->getTranslate($original);

            //replace original to translate phrase
            $content = substr_replace($content, $translate, $pos, strlen($original));
        }

        return $content;
    }

    /**
     * Get RegEx for parse HTML and get all phrases for translate
     * @return string
     */
    abstract public function getFindPhrasesRegex();
}
