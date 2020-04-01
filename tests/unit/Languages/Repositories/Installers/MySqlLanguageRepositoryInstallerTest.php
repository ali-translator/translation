<?php

namespace ALI\Translation\Tests\unit\Languages\Repositories\Installers;

use ALI\Translation\Languages\Repositories\Installers\MySqlLanguageRepositoryInstaller;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class MySqlLanguageRepositoryInstallerTest extends TestCase
{
    /**
     * @var MySqlLanguageRepositoryInstaller
     */
    private $languageRepositoryInstaller;

    /**
     * Test
     */
    public function test()
    {
        $sourceFactory = new SourceFactory();

        $connection = $sourceFactory->createPDO();
        $this->languageRepositoryInstaller = new MySqlLanguageRepositoryInstaller($connection);

        if ($this->languageRepositoryInstaller->isInstalled()) {
            $this->languageRepositoryInstaller->destroy();
        }

        $this->install($this->languageRepositoryInstaller);
        $this->destroy($this->languageRepositoryInstaller);
    }

    /**
     * @param MySqlLanguageRepositoryInstaller $MySqlLanguageRepositoryInstaller
     */
    private function install($MySqlLanguageRepositoryInstaller)
    {
        $this->assertFalse($MySqlLanguageRepositoryInstaller->isInstalled());
        $MySqlLanguageRepositoryInstaller->install();
        $this->assertTrue($MySqlLanguageRepositoryInstaller->isInstalled());
    }

    /**
     * @param MySqlLanguageRepositoryInstaller $MySqlLanguageRepositoryInstaller
     */
    private function destroy($MySqlLanguageRepositoryInstaller)
    {
        $MySqlLanguageRepositoryInstaller->destroy();
        $this->assertFalse($MySqlLanguageRepositoryInstaller->isInstalled());
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        if ($this->languageRepositoryInstaller->isInstalled()) {
            $this->languageRepositoryInstaller->destroy();
        }
    }
}
