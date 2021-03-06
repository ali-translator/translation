<?php

namespace ALI\Translation\Translate\MissingTranslateCallbacks;

use ALI\Translation\Translate\PhraseCollection\OriginalPhraseCollection;
use ALI\Translation\Translate\PlainTranslator\PlainTranslatorInterface;

/**
 * Class
 */
class CollectorMissingTranslatesCallback
{
    /**
     * @var OriginalPhraseCollection
     */
    private $originalPhraseCollection;

    /**
     * @param OriginalPhraseCollection $originalPhrasePacket
     */
    public function __construct(OriginalPhraseCollection $originalPhrasePacket = null)
    {
        $this->originalPhraseCollection = $originalPhrasePacket ?: new OriginalPhraseCollection();
    }

    /**
     * @param string $searchPhrase
     * @param PlainTranslatorInterface $translator
     */
    public function __invoke($searchPhrase, $translator)
    {
        $this->originalPhraseCollection->add($searchPhrase);
    }

    /**
     * @return OriginalPhraseCollection
     */
    public function getOriginalPhraseCollection()
    {
        return $this->originalPhraseCollection;
    }
}
