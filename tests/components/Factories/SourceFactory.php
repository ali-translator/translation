<?php

namespace ALI\Translation\Tests\components\Factories;

use ALI\Translation\Translate\Language\Language;
use ALI\Translation\Translate\Sources\CsvFileSource;
use ALI\Translation\Translate\Sources\Installers\MySqlSourceInstaller;
use ALI\Translation\Translate\Sources\MySqlSource;
use PDO;

/**
 * SourceFactory
 */
class SourceFactory
{
    /**
     * @return PDO
     */
    public function createPDO()
    {
        $connection = new PDO(SOURCE_MYSQL_DNS, SOURCE_MYSQL_USER, SOURCE_MYSQL_PASSWORD);
        $connection->setAttribute(PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION);

        return $connection;
    }

    /**
     * @param Language $originalLanguage
     * @return array [MySqlSource, MySqlSourceInstaller]
     */
    public function createMysqlSource(Language $originalLanguage)
    {
        $connection = $this->createPDO();
        $mySqlSourceInstaller = new MySqlSourceInstaller($connection);
        if (!$mySqlSourceInstaller->isInstalled()) {
            $mySqlSourceInstaller->install();
        }

        $mySqlSource = new MySqlSource($connection, $originalLanguage);

        return [$mySqlSource, $mySqlSourceInstaller];
    }

    /**
     * @param Language $originalLanguage
     * @return CsvFileSource
     */
    public function createCsvSource(Language $originalLanguage)
    {
        $csvFileSource = new CsvFileSource(SOURCE_CSV_PATH, $originalLanguage);

        return $csvFileSource;
    }
}
