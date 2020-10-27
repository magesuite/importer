<?php

namespace MageSuite\Importer\Test\Unit\Command;

class CommandFactoryTest extends \PHPUnit\Framework\TestCase
{
    private $factory;

    public function setUp(): void
    {
        $this->factory = \Magento\TestFramework\ObjectManager::getInstance()->create(\MageSuite\Importer\Services\Command\Factory::class);
    }

    public function testItImplementsCommandFactoryInterface() {
        $this->assertInstanceOf(\MageSuite\Importer\Command\CommandFactory::class, $this->factory);
    }

    public function testItReturnsNullWhenCommandDoesNotExist() {
        $this->assertNull($this->factory->create('non_existing_command'));
    }

    /**
     * @dataProvider getTypesToCommandClassesMapping
     */
    public function testItReturnsCorrectCommand($type, $expectedClass) {
        $this->assertInstanceOf($expectedClass, $this->factory->create($type));
    }

    public static function getTypesToCommandClassesMapping() {
        return [
            ['download', \MageSuite\Importer\Command\File\Download::class],
            ['download_newest', \MageSuite\Importer\Command\File\DownloadNewest::class],
            ['sync', \MageSuite\Importer\Command\File\Sync::class],
            ['parse', \MageSuite\Importer\Command\Import\Parse::class],
            ['map_images', \MageSuite\Importer\Command\Import\MapImages::class],
            ['import', \MageSuite\Importer\Command\Import\Import::class],
            ['create_directories', \MageSuite\Importer\Command\File\CreateDirectories::class],
            ['disable_indexers', \MageSuite\Importer\Command\Magento\DisableIndexers::class],
            ['enable_indexers', \MageSuite\Importer\Command\Magento\EnableIndexers::class],
            ['copy', \MageSuite\Importer\Command\File\Copy::class],
            ['move', \MageSuite\Importer\Command\File\Move::class],
            ['delete', \MageSuite\Importer\Command\File\Delete::class],
        ];
    }
}
