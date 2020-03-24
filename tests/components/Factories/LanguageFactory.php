<?php

namespace ALI\Translation\Tests\components\Factories;

use ALI\Translation\Translate\Language\Language;

/**
 * Class
 */
class LanguageFactory
{
    const ORIGINAL_LANGUAGE_ALIAS = 'en';
    const ORIGINAL_LANGUAGE_TITLE = 'English';

    const CURRENT_LANGUAGE_ALIAS = 'ua';
    const CURRENT_LANGUAGE_TITLE = 'Ukrainian';

    /**
     * @return Language[]
     */
    public function createOriginalAndCurrentLanguage()
    {
        $originalLanguage = $this->getOriginalLanguage();
        $currentLanguage = $this->getCurrentLanguage();

        return [$originalLanguage, $currentLanguage];
    }

    /**
     * @return Language
     */
    public function getOriginalLanguage()
    {
        return new Language(self::ORIGINAL_LANGUAGE_ALIAS, self::ORIGINAL_LANGUAGE_TITLE);
    }

    /**
     * @return Language
     */
    public function getCurrentLanguage()
    {
        return new Language(self::CURRENT_LANGUAGE_ALIAS, self::CURRENT_LANGUAGE_TITLE);
    }
}
