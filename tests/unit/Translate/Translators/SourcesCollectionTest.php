<?php

namespace ALI\Translation\Tests\unit\Translate\Translators;

use ALI\Translation\Tests\components\Factories\LanguageFactory;
use ALI\Translation\Tests\components\Factories\SourceFactory;
use ALI\Translation\Translate\Sources\CsvFileSource;
use ALI\Translation\Translate\Sources\MySqlSource;
use ALI\Translation\Translate\Translators\SourcesCollection;
use PHPUnit\Framework\TestCase;

/**
 * Class
 */
class SourcesCollectionTest extends TestCase
{
    public function test()
    {
        $sourceFactory = new SourceFactory();

        $sourceCollection = new SourcesCollection();
        $sourceCollection->addSource(
            $sourceFactory->generateSource(SourceFactory::SOURCE_CSV, 'en'),
            ['ua']
        );
        $sourceCollection->addSource(
            $sourceFactory->generateSource(SourceFactory::SOURCE_MYSQL, 'en')
        );

        $this->assertInstanceOf(CsvFileSource::class, $sourceCollection->getSource(LanguageFactory::ORIGINAL_LANGUAGE_ALIAS, 'ua'));
        $this->assertInstanceOf(MySqlSource::class, $sourceCollection->getSource(LanguageFactory::ORIGINAL_LANGUAGE_ALIAS, 'ru'));
        $this->assertInstanceOf(MySqlSource::class, $sourceCollection->getSource(LanguageFactory::ORIGINAL_LANGUAGE_ALIAS, 'cs'));
    }
}
