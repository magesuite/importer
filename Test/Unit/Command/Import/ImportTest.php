<?php

namespace MageSuite\Importer\Test\Unit\Command\Import;

class ImportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\Importer\Command\Import\Import
     */
    private $command;

    private $importerMock;

    public function setUp() {
        $this->importerMock = $this
            ->getMockBuilder(\MageSuite\Importer\Model\Import\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new \MageSuite\Importer\Command\Import\Import($this->importerMock);
    }

    public function testItImplementsCommandInterface() {
        $this->assertInstanceOf(\MageSuite\Importer\Command\Command::class, $this->command);
    }

    public function testItPassesPathsProperlyWithDefaultOptions() {

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
            ->with(BP . DIRECTORY_SEPARATOR . 'var/import', \MageSuite\Importer\Model\Import\Product::BEHAVIOR_UPDATE);

        $this->command->execute($configuration);
    }

    public function testItSetsValidationStrategyProperly() {
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

    public function testItSetsBehaviorProperly() {
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
}