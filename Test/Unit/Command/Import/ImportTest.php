<?php

namespace MageSuite\Importer\Test\Unit\Command\Import;

class ImportTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\Importer\Command\Import\Import $command = null;
    protected ?\PHPUnit\Framework\MockObject\MockObject $importerMock = null;
    protected ?\Magento\Framework\App\ResourceConnection $resourceConnection = null;

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->importerMock = $this
            ->getMockBuilder(\MageSuite\Importer\Model\Import\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->addSharedInstance($this->importerMock, \MageSuite\Importer\Model\Import\Product::class);
        $this->command = $objectManager->create(\MageSuite\Importer\Command\Import\Import::class);
        $this->resourceConnection = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
    }

    public function testItImplementsCommandInterface()
    {
        $this->assertInstanceOf(\MageSuite\Importer\Command\Command::class, $this->command);
    }

    public function testItPassesPathsProperlyWithDefaultOptions()
    {

        $configuration = [
            'source_path' => 'var/import',
            'images_directory_path' => 'var/import/images',
        ];

        $this->importerMock
            ->expects($this->once())
            ->method('setImportImagesFileDir')
            ->with('var/import/images');

        $this->importerMock
            ->expects($this->once())
            ->method('setValidationStrategy')
            ->with(\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR);

        $this->importerMock
            ->expects($this->once())
            ->method('importFromFile')
            ->with(BP . DIRECTORY_SEPARATOR . 'var/import', \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE);

        $this->command->execute($configuration);
    }

    public function testItSetsValidationStrategyProperly()
    {
        $configuration = [
            'source_path' => 'var/import',
            'images_directory_path' => 'var/import/images',
            'validation_strategy' => 'skip'
        ];

        $this->importerMock
            ->expects($this->once())
            ->method('setValidationStrategy')
            ->with(\Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS);

        $this->command->execute($configuration);
    }

    public function testItSetsBehaviorProperly()
    {
        $configuration = [
            'source_path' => 'var/import',
            'images_directory_path' => 'var/import/images',
            'behavior' => 'sync'
        ];

        $this->importerMock
            ->expects($this->once())
            ->method('importFromFile')
            ->with(BP . DIRECTORY_SEPARATOR . 'var/import', \MageSuite\Importer\Model\Import\Product::BEHAVIOR_SYNC);

        $this->command->execute($configuration);
    }

    public function testIfImportDataTableIsFlushedAfterImport(): void
    {
        $configuration = [
            'source_path' => 'var/import',
            'images_directory_path' => 'var/import/images',
            'behavior' => 'sync'
        ];

        $this->command->execute($configuration);

        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('importexport_importdata');
        $select = $connection->select()->from($tableName);
        $this->assertEmpty($connection->fetchAll($select));
    }
}
