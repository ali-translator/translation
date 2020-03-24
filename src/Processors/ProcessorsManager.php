<?php

namespace ALI\Translation\Processors;

use ALI\Translation\Processors\PreProcessors\PreProcessorInterface;
use ALI\Translation\Processors\TranslateProcessors\TranslateProcessors;
use ALI\Translation\Translate\Translators\TranslatorInterface;

/**
 * ProcessorsManager
 */
class ProcessorsManager
{
    /**
     * @var PreProcessorInterface[]
     */
    protected $preProcessors = [];

    /**
     * @var TranslateProcessors[]
     */
    protected $translateProcessors = [];

    /**
     * @param PreProcessorInterface[] $preProcessors
     * @param TranslateProcessors[] $translateProcessors
     */
    public function __construct(array $preProcessors = [], array $translateProcessors = [])
    {
        $this->preProcessors = $preProcessors;
        $this->translateProcessors = $translateProcessors;
    }

    /**
     * @param PreProcessorInterface $preProcessors
     */
    public function addPreProcessor(PreProcessorInterface $preProcessors)
    {
        $this->preProcessors[] = $preProcessors;
    }

    /**
     * @param TranslateProcessors $translateProcessors
     */
    public function addTranslateProcessor(TranslateProcessors $translateProcessors)
    {
        $this->translateProcessors[] = $translateProcessors;
    }

    /**
     * @param string $content
     * @param TranslatorInterface $translate
     * @return string
     */
    public function executeProcesses($content, TranslatorInterface $translate)
    {
        $cleanBuffer = $this->executePreProcesses($content);
        $content = $this->executeTranslateProcesses($content, $cleanBuffer, $translate);

        return $content;
    }

    /**
     * @param string $content
     * @return string
     */
    public function executePreProcesses($content)
    {
        $cleanBuffer = $content;
        foreach ($this->preProcessors as $preProcessor) {
            $cleanBuffer = $preProcessor->process($cleanBuffer);
        }

        return $content;
    }

    /**
     * @param string $content
     * @param string $cleanBuffer
     * @param TranslatorInterface $translator
     * @return string
     */
    public function executeTranslateProcesses($content, $cleanBuffer, TranslatorInterface $translator)
    {
        foreach ($this->translateProcessors as $translateProcessor) {
            $content = $translateProcessor->process($content, $cleanBuffer, $translator);
        }

        return $content;
    }
}
