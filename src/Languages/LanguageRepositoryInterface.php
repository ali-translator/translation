<?php

namespace ALI\Translation\Languages;

/**
 * LanguageRepositoryInterface
 */
interface LanguageRepositoryInterface
{
    /**
     * @param LanguageInterface $language
     * @param bool $isActive
     * @return mixed
     */
    public function save(LanguageInterface $language, $isActive);

    /**
     * @param string $alias
     * @return null|LanguageInterface
     */
    public function find($alias);

    /**
     * @param bool $onlyActive
     * @return LanguageInterface[]
     */
    public function getAll($onlyActive = true);
}
