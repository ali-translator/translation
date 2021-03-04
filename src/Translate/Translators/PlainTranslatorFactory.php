<?php

namespace ALI\Translation\Translate\Translators;

use ALI\Translation\Translate\Source\SourceInterface;

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
