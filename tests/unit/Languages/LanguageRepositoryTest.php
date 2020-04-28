<?php

namespace ALI\Translation\Tests\Languages;

use ALI\Translation\Languages\Language;
use ALI\Translation\Languages\LanguageRepositoryInterface;
use ALI\Translation\Languages\Repositories\ArrayLanguageRepository;
use ALI\Translation\Languages\Repositories\Installers\MySqlLanguageRepositoryInstaller;
use ALI\Translation\Languages\Repositories\MySqlLanguageRepository;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class LanguageRepositoryTest extends TestCase
{
    /**
     * Test
     */
    public function test()
    {
        $this->checkMySqlLanguageRepository();
        $this->checkArrayLanguageRepository();
    }

    /**
     * Check MySqlLanguageRepository
     */
    private function checkMySqlLanguageRepository()
    {
        $connection = (new SourceFactory())->createPDO();
        $languageRepositoryInstaller = new MySqlLanguageRepositoryInstaller($connection);

        if ($languageRepositoryInstaller->isInstalled()) {
            $languageRepositoryInstaller->destroy();
        }

        $languageRepositoryInstaller->install();
        $languageRepository = new MySqlLanguageRepository($connection);

        $this->checkLanguageRepository($languageRepository);

        $languageRepositoryInstaller->destroy();
    }

    /**
     * Check ArrayLanguageRepository
     */
    private function checkArrayLanguageRepository()
    {
        $arrayRepository = new ArrayLanguageRepository();
        $this->checkLanguageRepository($arrayRepository);
    }

    /**
     * Check LanguageRepository by Interface
     */
    private function checkLanguageRepository(LanguageRepositoryInterface $languageRepository)
    {
        $this->assertEmpty($languageRepository->getAll(false));
        $this->assertEmpty($languageRepository->getAll(true));

        $languageUkraine = new Language('ua', 'Ukraine');
        $languageRepository->save($languageUkraine, false);

        $this->assertEquals($languageRepository->getAll(false), [$languageUkraine]);
        $this->assertEmpty($languageRepository->getAll(true));

        $languageRepository->save($languageUkraine, true);

        $this->assertEquals($languageRepository->getAll(false), [$languageUkraine]);
        $this->assertEquals($languageRepository->getAll(true), [$languageUkraine]);

        $languageEnglish = new Language('en', 'English');
        $languageRepository->save($languageEnglish, false);

        $this->assertCount(2,$languageRepository->getAll(false));

        $this->assertEquals($languageRepository->find($languageUkraine->getAlias()), $languageUkraine);
        $this->assertEquals($languageRepository->find($languageEnglish->getAlias()), $languageEnglish);
    }
}
