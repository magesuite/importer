<?php

namespace MageSuite\Importer\Test\Integration\Observer;

class TransactionCommitAndRollbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected $pathToImportFiles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->pathToImportFiles = str_replace(BP, '', __DIR__) . '/../_files/';
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store general/file/bunch_size 1
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testItRollbacksProductsCorrectly()
    {
        \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\ImportExport\Model\ResourceModel\Import\Data::class);
        $expected = [
            'simple1' => 'Simple 1',
            'simple2' => 'Simple Product2',
            'simple3' => 'Simple 3'
        ];

        $importCommand = $this->objectManager->create(\MageSuite\Importer\Command\Import\Import::class);
        $importCommand->execute([
            'source_path' => $this->pathToImportFiles . 'import_fail.json',
            'behavior' => 'update'
        ]);

        $productRepository = $this->objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        foreach ($expected as $productSku => $productName) {
            $product = $productRepository->get($productSku);
            $this->assertEquals($productName, $product->getName());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testItImportsProductCorrectly()
    {
        $expected = [
            'simple1' => 'Simple 1',
            'simple2' => 'Simple 2',
            'simple3' => 'Simple 3'
        ];

        $importCommand = $this->objectManager->create(\MageSuite\Importer\Command\Import\Import::class);
        $importCommand->execute([
            'source_path' => $this->pathToImportFiles . 'import.json',
            'behavior' => 'update'
        ]);

        $productRepository = $this->objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        foreach ($expected as $productSku => $productName) {
            $product = $productRepository->get($productSku);
            $this->assertEquals($productName, $product->getName());
        }
    }
}
