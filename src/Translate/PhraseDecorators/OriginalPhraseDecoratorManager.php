<?php

namespace ALI\Translation\Translate\PhraseDecorators;

use ALI\Translation\Translate\PhraseDecorators\OriginalDecorators\OriginalPhraseDecoratorInterface;

/**
 * Class
 */
class OriginalPhraseDecoratorManager implements OriginalPhraseDecoratorInterface
{
    /**
     * @var OriginalPhraseDecoratorInterface[]
     */
    protected $originalDecorators = [];

    /**
     * @param OriginalPhraseDecoratorInterface[] $originalDecorators
     */
    public function __construct(array $originalDecorators = [])
    {
        $this->originalDecorators = $originalDecorators;
    }

    /**
     * @param string $original
     * @return string
     */
    public function decorate($original)
    {
        foreach ($this->originalDecorators as $originalDecorator) {
            $original = $originalDecorator->decorate($original);
        }

        return $original;
    }

    /**
     * @return OriginalPhraseDecoratorInterface[]
     */
    public function getOriginalDecorators()
    {
        return $this->originalDecorators;
    }

    /**
     * @param OriginalPhraseDecoratorInterface[] $originalDecorators
     */
    public function setOriginalDecorators($originalDecorators)
    {
        $this->originalDecorators = $originalDecorators;
    }
}
