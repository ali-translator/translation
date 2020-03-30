<?php

namespace ALI\Translation\Tests\unit\Sources;

use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Translate\Sources\Installers\MySqlSourceInstaller;
use PHPUnit\Framework\TestCase;

/**
 * MySqlSourceInstallerTest
 */
class MySqlSourceInstallerTest extends TestCase
{
    /**
     * @var MySqlSourceInstaller
     */
    private $mySqlSourceInstaller;

    /**
     * Test
     */
    public function test()
    {
        $sourceFactory = new SourceFactory();

        $connection = $sourceFactory->createPDO();
        $mySqlSourceInstaller = new MySqlSourceInstaller($connection);
        $this->mySqlSourceInstaller = $mySqlSourceInstaller;

        if ($this->mySqlSourceInstaller->isInstalled()) {
            $this->mySqlSourceInstaller->destroy();
        }

        $this->install($mySqlSourceInstaller);
        $this->destroy($mySqlSourceInstaller);
    }

    /**
     * @param MySqlSourceInstaller $mySqlSourceInstaller
     */
    private function install($mySqlSourceInstaller)
    {
        $this->assertFalse($mySqlSourceInstaller->isInstalled());
        $mySqlSourceInstaller->install();
        $this->assertTrue($mySqlSourceInstaller->isInstalled());
    }

    /**
     * @param MySqlSourceInstaller $mySqlSourceInstaller
     */
    private function destroy($mySqlSourceInstaller)
    {
        $mySqlSourceInstaller->destroy();
        $this->assertFalse($mySqlSourceInstaller->isInstalled());
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        if ($this->mySqlSourceInstaller->isInstalled()) {
            $this->mySqlSourceInstaller->destroy();
        }
    }
}
