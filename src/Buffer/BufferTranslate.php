<?php

namespace ALI\Translation\Buffer;

use ALI\Translation\Buffer\KeyGenerators\StaticKeyGenerator;
use ALI\Translation\ContentProcessors\ContentProcessorsManager;
use ALI\Translation\Translate\PhraseCollection\OriginalPhraseCollection;
use ALI\Translation\Translate\PhraseCollection\TranslatePhraseCollection;
use ALI\Translation\Translate\Source\TemporarySourceInterface;
use ALI\Translation\Translate\PlainTranslator\PlainTranslatorFactory;
use ALI\Translation\Translate\PlainTranslator\PlainTranslatorInterface;

/**
 * Class BufferTranslate
 */
class BufferTranslate
{
    /**
     * Translates all buffer contents, and replace their in parent content
     *
     * @param BufferContent $bufferContent
     * @param PlainTranslatorInterface $translator
     * @return string
     */
    public function translateChildContentCollection(BufferContent $bufferContent, PlainTranslatorInterface $translator)
    {
        if (!$bufferContent->getChildContentCollection()) {
            return $bufferContent->getContentString();
        }

        $originalsCollection = $this->prepareOriginals($bufferContent);
        $translatedCollection = $translator->translateAll($originalsCollection->getAll());

        return $this->replaceBufferByTranslatedPacket($bufferContent, $translatedCollection);
    }

    /**
     * @param BufferContent                 $bufferContent
     * @param OriginalPhraseCollection|null $originals
     * @return OriginalPhraseCollection
     */
    private function prepareOriginals(BufferContent $bufferContent, OriginalPhraseCollection $originals = null)
    {
        $originals = $originals ?: new OriginalPhraseCollection();
        foreach ($bufferContent->getChildContentCollection()->getBuffersContent() as $childBufferContent) {
            if ($childBufferContent->withContentTranslation()) {
                $originals->add($childBufferContent->getContentString());
            }
            if ($childBufferContent->getChildContentCollection()) {
                $originals = $this->prepareOriginals($childBufferContent, $originals);
            }
        }
        return $originals;
    }

    /**
     * Replace content by buffers translation. Which pieces buffer content will be translated detecting processorsManager.
     * Method makes N requests to translator source, where N = (buffers count * active processors)
     * if for your source are sensitive for request count - use method "translateContentByOneRequest"
     * @param BufferContent $bufferContent
     * @param PlainTranslatorInterface $translator
     * @return string
     * @see translateBuffersWithProcessorsByOneRequest
     *
     */
    public function translateBuffersWithProcessors(BufferContent $bufferContent, PlainTranslatorInterface $translator, ContentProcessorsManager $contentProcessorsManager)
    {
        $buffer = $bufferContent->getChildContentCollection();
        if (!$buffer) {
            return $bufferContent->getContentString();
        }
        $content = $bufferContent->getContentString();

        // Translate main content
        $content = $contentProcessorsManager->executeProcesses($content, $translator);

        // Translate child content
        foreach ($buffer->getBuffersContent() as $bufferId => $childBufferContent) {
            $bufferKey = $buffer->generateBufferKey($bufferId);

            // resolve child buffers
            if ($childBufferContent->withContentTranslation()) {
                $translatedSting = $contentProcessorsManager->executeProcesses($childBufferContent->getContentString(), $translator);
            } else {
                $translatedSting = $childBufferContent->getContentString();
            }
            if ($childBufferContent->getChildContentCollection()) {
                $translatedSting = $this->translateBuffersWithProcessors(new BufferContent($translatedSting, $childBufferContent->getChildContentCollection()), $translator, $contentProcessorsManager);
            }
            $content = str_replace(
                $bufferKey,
                $translatedSting,
                $content
            );
            $buffer->remove($bufferId);
        }

        return $content;
    }

    /**
     * Optimization for method "translateBuffersWithProcessors"
     * @param BufferContent $bufferContent
     * @param PlainTranslatorInterface $translator
     * @return string
     *@see translateBuffersWithProcessors     *
     *
     * If you has many buffers, and source sensitive for request count,
     * this method may decrease request numbers to one.
     * But this method create more php actions with content replacing
     *
     */
    public function translateBuffersWithProcessorsByOneRequest(BufferContent $bufferContent, PlainTranslatorInterface $translator, ContentProcessorsManager $contentProcessorsManager)
    {
        // Init additional objects
        $bufferLayer = new BufferContentCollection(new StaticKeyGenerator('#ali-buffer-layer-content_', '#'));

        $temporarySource = new TemporarySourceInterface($translator->getSource()->getOriginalLanguageAlias(), $bufferLayer);
        $bufferLayerTranslator = (new PlainTranslatorFactory())->createPlainTranslator($temporarySource, $translator->getTranslationLanguageAlias());

        // Create additional buffering layer
        $layerContent = $this->translateBuffersWithProcessors($bufferContent, $bufferLayerTranslator, $contentProcessorsManager);

        return $this->translateChildContentCollection(new BufferContent($layerContent, $bufferLayer), $translator);
    }

    /**
     * @param BufferContent             $bufferContent
     * @param TranslatePhraseCollection $translatePhrasePacket
     * @return string|string[]
     */
    private function replaceBufferByTranslatedPacket(BufferContent $bufferContent, TranslatePhraseCollection $translatePhrasePacket)
    {
        $buffer = $bufferContent->getChildContentCollection();
        $content = $bufferContent->getContentString();

        $forReplacing = [];
        foreach ($buffer->getBuffersContent() as $bufferId => $bufferContent) {
            $bufferKey = $buffer->generateBufferKey($bufferId);

            if ($bufferContent->withContentTranslation()) {
                $translatedSting = $translatePhrasePacket->getTranslate($bufferContent->getContentString()) ?: $bufferContent->getContentString();
            } else {
                $translatedSting = $bufferContent->getContentString();
            }

            if ($bufferContent->getChildContentCollection()) {
                $translatedSting = $this->replaceBufferByTranslatedPacket(new BufferContent($translatedSting, $bufferContent->getChildContentCollection()), $translatePhrasePacket);
            }
            $forReplacing[$bufferKey] = $translatedSting;
        }

        $replacedIds = [];
        $content = preg_replace_callback(
            $buffer->getKeyGenerator()->getRegularExpression(),
            function ($matches) use (&$forReplacing) {
                $replacedIds[] = $matches['id'];
                return $forReplacing[$matches[0]];
            },
            $content);

        foreach ($replacedIds as $replacedId) {
            $buffer->remove($replacedId);
        }

        return $content;
    }
}
