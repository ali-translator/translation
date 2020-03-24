<?php

namespace ALI\Translation\Buffer;

use ALI\Translation\Buffer\KeyGenerators\StaticKeyGenerator;
use ALI\Translation\Processors\ProcessorsManager;
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
        if (!$bufferContent->getBuffer()) {
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
        foreach ($bufferContent->getBuffer()->getBuffersContent() as $childBufferContent) {
            $originalsPacket->add($childBufferContent->getContentString());
            if ($childBufferContent->getBuffer()) {
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
    public function translateBuffersWithProcessors(BufferContent $bufferContent, TranslatorInterface $translator, ProcessorsManager $processorsManager)
    {
        $buffer = $bufferContent->getBuffer();
        if (!$buffer) {
            return $bufferContent->getContentString();
        }
        $content = $bufferContent->getContentString();

        foreach ($buffer->getBuffersContent() as $bufferId => $childBufferContent) {
            $bufferKey = $buffer->generateBufferKey($bufferId);

            // resolve child buffers
            $translatedSting = $processorsManager->executeProcesses($childBufferContent->getContentString(), $translator);
            if ($childBufferContent->getBuffer()) {
                $translatedSting = $this->translateBuffersWithProcessors(new BufferContent($translatedSting, $childBufferContent->getBuffer()), $translator, $processorsManager);
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
    public function translateBuffersWithProcessorsByOneRequest(BufferContent $bufferContent, TranslatorInterface $translator, ProcessorsManager $processorsManager)
    {
        // Init additional objects
        $bufferLayer = new Buffer(new StaticKeyGenerator('#ali-buffer-layer-content_', '#'));

        $fakeBufferSource = new FakeBufferSource($translator->getSource()->getOriginalLanguage(), $bufferLayer);
        $bufferLayerTranslator = new Translator($translator->getLanguage(),$fakeBufferSource);

        // Create additional buffering layer
        $layerContent = $this->translateBuffersWithProcessors($bufferContent, $bufferLayerTranslator, $processorsManager);

        return $this->translateBuffer(new BufferContent($layerContent, $bufferLayer), $translator);
    }

    /**
     * @param BufferContent $bufferContent
     * @param TranslatePhrasePacket $translatePhrasePacket
     * @return string|string[]
     */
    private function replaceBufferByTranslatedPacket(BufferContent $bufferContent, TranslatePhrasePacket $translatePhrasePacket)
    {
        $buffer = $bufferContent->getBuffer();
        $content = $bufferContent->getContentString();
        foreach ($buffer->getBuffersContent() as $bufferId => $bufferContent) {
            $bufferKey = $buffer->generateBufferKey($bufferId);
            $translatedSting = $translatePhrasePacket->getTranslate($bufferContent->getContentString()) ?: $bufferContent->getContentString();
            if ($bufferContent->getBuffer()) {
                $translatedSting = $this->replaceBufferByTranslatedPacket(new BufferContent($translatedSting, $bufferContent->getBuffer()), $translatePhrasePacket);
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
