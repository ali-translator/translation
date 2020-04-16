<?php

namespace ALI\Translation\Translate\MissingTranslateCallbacks;

use ALI\Translation\Translate\PhrasePackets\OriginalPhrasePacket;
use ALI\Translation\Translate\Translators\TranslatorInterface;

/**
 * Class
 */
class CollectorMissingTranslatesCallback
{
    /**
     * @var OriginalPhrasePacket
     */
    private $originalPhrasePacket;

    /**
     * @param OriginalPhrasePacket $originalPhrasePacket
     */
    public function __construct(OriginalPhrasePacket $originalPhrasePacket = null)
    {
        $this->originalPhrasePacket = $originalPhrasePacket ?: new OriginalPhrasePacket();
    }

    /**
     * @param string $searchPhrase
     * @param TranslatorInterface $translator
     */
    public function __invoke($searchPhrase, $translator)
    {
        $this->originalPhrasePacket->add($searchPhrase);
    }

    /**
     * @return OriginalPhrasePacket
     */
    public function getOriginalPhrasePacket()
    {
        return $this->originalPhrasePacket;
    }
}
