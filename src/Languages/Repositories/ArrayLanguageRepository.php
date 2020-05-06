<?php

namespace ALI\Translation\Languages\Repositories;

use ALI\Translation\Languages\LanguageInterface;
use ALI\Translation\Languages\LanguageRepositoryInterface;

/**
 * This repository may use, when your save your languages in config file,
 * and create object from by config data
 */
class ArrayLanguageRepository implements LanguageRepositoryInterface
{
    /**
     * @var LanguageInterface[]
     */
    protected $activeLanguages = [];

    /**
     * @var LanguageInterface[]
     */
    protected $inactiveLanguages = [];

    /**
     * @inheritDoc
     */
    public function save(LanguageInterface $language, $isActive)
    {
        if ($isActive) {
            $this->activeLanguages[$language->getAlias()] = $language;
            if (isset($this->inactiveLanguages[$language->getAlias()])) {
                unset($this->inactiveLanguages[$language->getAlias()]);
            }
        } else {
            $this->inactiveLanguages[$language->getAlias()] = $language;
            if (isset($this->activeLanguages[$language->getAlias()])) {
                unset($this->activeLanguages[$language->getAlias()]);
            }
        }
    }

    /**
     * @param string $alias
     * @return LanguageInterface|null
     */
    public function find($alias)
    {
        if (isset($this->activeLanguages[$alias])) {
            return $this->activeLanguages[$alias];
        }

        if (isset($this->inactiveLanguages[$alias])) {
            return $this->inactiveLanguages[$alias];
        }

        return null;
    }

    /**
     * @param bool $onlyActive
     * @return LanguageInterface[]
     */
    public function getAll($onlyActive = true)
    {
        if ($onlyActive) {
            return array_values($this->activeLanguages);
        }

        return array_values($this->activeLanguages + $this->inactiveLanguages);
    }
}
