<?php

namespace ALI\Translation\Translate\PlainTranslator;

use ALI\Translation\Translate\Source\SourceInterface;
use ALI\Translation\Translate\Source\SourcesCollection;
use ALI\Translation\Translate\Translator;

class PlainTranslatorFactory
{
    /**
     * @param SourceInterface $source
     * @param $translationLanguageAlias
     * @return PlainTranslator
     */
    public function createPlainTranslator(SourceInterface $source, $translationLanguageAlias)
    {
        $sourceCollection = new SourcesCollection();
        $sourceCollection->addSource($source);

        return new PlainTranslator(
            $translationLanguageAlias,
            $source->getOriginalLanguageAlias(),
            new Translator($sourceCollection)
        );
    }
}
