<?php

namespace ALI\Translation\Processors\TranslateProcessors;

use ALI\Translation\Translate\Translators\TranslatorInterface;

/**
 * Class AbstractHtmlProcessor
 * @package ALI\Translation\Processors\TranslateProcessors
 */
abstract class AbstractHtmlProcessor implements TranslateProcessors
{
    /**
     * Be careful if you want to change this parameter to false.
     * Without html entity encode you can break HTML syntax.
     * It can also be the cause of malicious JS code on the site.
     * Change to false only if you fully trust the translation source.
     * @var bool
     */
    protected $applyHtmlEntityEncode = true;

    /**
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * @param string $content
     * @param string $cleanContent
     * @param TranslatorInterface $translator
     * @return string
     * @throws \ALI\Translation\Exceptions\ALIException
     */
    public function process($content, $cleanContent, TranslatorInterface $translator)
    {
        if ($translator->isCurrentLanguageOriginal()) {
            return $content;
        }

        preg_match_all($this->getFindPhrasesRegex(), $cleanContent, $match);
        $originalData = [
            'match'    => $match[0],
            'original' => $match['original'],
        ];

        $pos = 0;
        $translatePhrasePacket = $translator->translateAll($originalData['original']);

        foreach ($originalData['original'] AS $k => $original) {

            //find original phrase position
            $pos = strpos($content, $originalData['match'][$k], $pos);
            preg_match($this->getFindPhrasesRegex(), $content, $matchPosition, PREG_OFFSET_CAPTURE, $pos);
            $pos = $matchPosition['original'][1];

            //don't replace if we don't have translation
            if (!$translatePhrasePacket->existTranslate($original)) {
                continue;
            }

            $translator = $translatePhrasePacket->getTranslate($original);

            if ($this->isApplyHtmlEntityEncode()) {
                $translator = htmlspecialchars($translator, ENT_QUOTES, $this->getCharset(), false);
            }

            //replace original to translate phrase
            $content = substr_replace($content, $translator, $pos, strlen($original));
        }

        return $content;
    }

    /**
     * Get RegEx for parse HTML and get all phrases for translate
     * @return string
     */
    abstract public function getFindPhrasesRegex();

    /**
     * @return bool
     */
    public function isApplyHtmlEntityEncode()
    {
        return $this->applyHtmlEntityEncode;
    }

    /**
     * Be careful if you want to change this parameter to false.
     * Without html entity encode you can break HTML syntax.
     * It can also be the cause of malicious JS code on the site.
     * Change to false only if you fully trust the translation source.
     * @param bool $applyHtmlEntityEncode
     * @return $this
     */
    public function setApplyHtmlEntityEncode($applyHtmlEntityEncode)
    {
        $this->applyHtmlEntityEncode = $applyHtmlEntityEncode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }
}
