<?php

namespace ALI\Translation\Buffer;

use ALI\Translation\Buffer\KeyGenerators\StaticKeyGenerator;
use ALI\Translation\ContentProcessors\ContentProcessorsManager;
use ALI\Translation\Translate\PhrasePackets\OriginalPhrasePacket;
use ALI\Translation\Translate\PhrasePackets\TranslatePhrasePacket;
use ALI\Translation\Translate\Sources\FakeBufferSource;
use ALI\Translation\Translate\Translators\Translator;
use ALI\Translation\Translate\Translators\TranslatorInterface;

/**
 * Class BufferTranslate
 * @package ALI
 */
class BufferTranslate
{
    /**
     * Translates all buffer contents, and replace their in parent content
     *
     * @param BufferContent $bufferContent
     * @param TranslatorInterface $translator
     * @return string
     */
    public function translateBuffer(BufferContent $bufferContent, TranslatorInterface $translator)
    {
        if (!$bufferContent->getBufferContentCollection()) {
            return $bufferContent->getContentString();
        }

        $originalsPacket = $this->collectOriginalPacket($bufferContent);
        $translatedPacket = $translator->translateAll($originalsPacket->getAll());

        return $this->replaceBufferByTranslatedPacket($bufferContent, $translatedPacket);
    }

    /**
     * @param BufferContent $bufferContent
     * @param OriginalPhrasePacket|null $originalsPacket
     * @return OriginalPhrasePacket
     */
    private function collectOriginalPacket(BufferContent $bufferContent, OriginalPhrasePacket $originalsPacket = null)
    {
        $originalsPacket = $originalsPacket ?: new OriginalPhrasePacket();
        foreach ($bufferContent->getBufferContentCollection()->getBuffersContent() as $childBufferContent) {
            if ($childBufferContent->withContentTranslation()) {
                $originalsPacket->add($childBufferContent->getContentString());
            }
            if ($childBufferContent->getBufferContentCollection()) {
                $originalsPacket = $this->collectOriginalPacket($childBufferContent, $originalsPacket);
            }
        }
        return $originalsPacket;
    }

    /**
     * Replace content by buffers translation. Which pieces buffer content will be translated detecting processorsManager.
     * Method makes N requests to translator source, where N = (buffers count * active processors)
     * if for your source are sensitive for request count - use method "translateContentByOneRequest"
     * @see translateBuffersWithProcessorsByOneRequest
     *
     * @param BufferContent $bufferContent
     * @param TranslatorInterface $translator
     * @return string
     */
    public function translateBuffersWithProcessors(BufferContent $bufferContent, TranslatorInterface $translator, ContentProcessorsManager $contentProcessorsManager)
    {
        $buffer = $bufferContent->getBufferContentCollection();
        if (!$buffer) {
            return $bufferContent->getContentString();
        }
        $content = $bufferContent->getContentString();

        foreach ($buffer->getBuffersContent() as $bufferId => $childBufferContent) {
            $bufferKey = $buffer->generateBufferKey($bufferId);

            // resolve child buffers
            if ($childBufferContent->withContentTranslation()) {
                $translatedSting = $contentProcessorsManager->executeProcesses($childBufferContent->getContentString(), $translator);
            } else {
                $translatedSting = $childBufferContent->getContentString();
            }
            if ($childBufferContent->getBufferContentCollection()) {
                $translatedSting = $this->translateBuffersWithProcessors(new BufferContent($translatedSting, $childBufferContent->getBufferContentCollection()), $translator, $contentProcessorsManager);
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
     * @see translateBuffersWithProcessors     *
     *
     * If you has many buffers, and source sensitive for request count,
     * this method may decrease request numbers to one.
     * But this method create more php actions with content replacing
     *
     * @param BufferContent $bufferContent
     * @param TranslatorInterface $translator
     * @return string
     */
    public function translateBuffersWithProcessorsByOneRequest(BufferContent $bufferContent, TranslatorInterface $translator, ContentProcessorsManager $contentProcessorsManager)
    {
        // Init additional objects
        $bufferLayer = new BufferContentCollection(new StaticKeyGenerator('#ali-buffer-layer-content_', '#'));

        $fakeBufferSource = new FakeBufferSource($translator->getSource()->getOriginalLanguageAlias(), $bufferLayer);
        $bufferLayerTranslator = new Translator($translator->getLanguageAlias(),$fakeBufferSource);

        // Create additional buffering layer
        $layerContent = $this->translateBuffersWithProcessors($bufferContent, $bufferLayerTranslator, $contentProcessorsManager);

        return $this->translateBuffer(new BufferContent($layerContent, $bufferLayer), $translator);
    }

    /**
     * @param BufferContent $bufferContent
     * @param TranslatePhrasePacket $translatePhrasePacket
     * @return string|string[]
     */
    private function replaceBufferByTranslatedPacket(BufferContent $bufferContent, TranslatePhrasePacket $translatePhrasePacket)
    {
        $buffer = $bufferContent->getBufferContentCollection();
        $content = $bufferContent->getContentString();
        foreach ($buffer->getBuffersContent() as $bufferId => $bufferContent) {
            $bufferKey = $buffer->generateBufferKey($bufferId);

            if ($bufferContent->withContentTranslation()) {
                $translatedSting = $translatePhrasePacket->getTranslate($bufferContent->getContentString()) ?: $bufferContent->getContentString();
            } else {
                $translatedSting = $bufferContent->getContentString();
            }

            if ($bufferContent->getBufferContentCollection()) {
                $translatedSting = $this->replaceBufferByTranslatedPacket(new BufferContent($translatedSting, $bufferContent->getBufferContentCollection()), $translatePhrasePacket);
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
}
