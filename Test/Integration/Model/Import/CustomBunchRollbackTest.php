<?php

namespace MageSuite\Importer\Test\Integration\Model\Import;

class CustomBunchRollbackTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\Framework\App\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Model\ProductRepository $productRepository;
    protected ?\Magento\Catalog\Model\CategoryRepository $categoryRepository;
    protected ?string $pathToImportFiles;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->pathToImportFiles = str_replace(BP, '', __DIR__) . '/../../_files/';

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $this->categoryRepository = $this->objectManager->create(\Magento\Catalog\Model\CategoryRepository::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store general/file/bunch_size 100
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testItRollbacksProductsWithEveryProductBeingCustomBunchCorrectly()
    {
        $importCommand = $this->objectManager->create(\MageSuite\Importer\Command\Import\Import::class);
        $importCommand->execute([
            'source_path' => $this->pathToImportFiles . 'import_custom_bunch.json',
            'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
            'images_directory_path' => __DIR__ . '/../../_files/images',
            'bunch_grouping_field' => 'bunch_id'
        ]);

        $productThatShouldNotBeImported = $this->productRepository->get('simple2');

        $this->assertEquals('Simple Product2', $productThatShouldNotBeImported->getName(), 'Product update was not rolledback correctly');

        foreach (['1', '3', '4'] as $productNumber) {
            $productThatShouldBeImported = $this->productRepository->get(sprintf('simple%s', $productNumber));

            $this->assertEquals(
                sprintf('Simple Product%s Updated', $productNumber),
                $productThatShouldBeImported->getName(),
                'Product with correct data was not updated'
            );
        }
    }

    protected function tearDown(): void
    {
        $categoryCollection = $this->objectManager->get(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        foreach ($categoryCollection as $category) {
            if ($category->getId() > 1 && !$category->getResource()->isForbiddenToDelete($category->getId())) {
                $this->categoryRepository->delete($category);
            }
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
