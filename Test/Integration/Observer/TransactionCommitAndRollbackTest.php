<?php

namespace MageSuite\Importer\Test\Integration\Observer;

class TransactionCommitAndRollbackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\AdobeStockAsset\Model\CategoryRepository
     */
    protected $categoryRepository;

    protected $pathToImportFiles = '';

    protected $imagesTypesToCheck = ['base_image', 'small_image', 'thumbnail_image'];

    protected $expectedValues = [
        'simple' => [
            'simple1' => [
                'general' => [
                    'sku' => 'simple1',
                    'name' => 'Simple 1',
                    'price' => 25,
                ],
                'categories' => [
                    'Category 1'
                ],
                'images' => [
                    'base_image' => '/0/1/01aaaa.png',
                    'small_image' => '/0/1/01bbbb.png',
                    'thumbnail_image' => '/0/1/01cccc.png'
                ]
            ],
            'simple2' => [
                'general' => [
                    'sku' => 'simple2',
                    'name' => 'Simple Product2',
                    'price' => 20,
                ],
                'categories' => [
                ],
                'images' => []
            ],
            'simple3' => [
                'general' => [
                    'sku' => 'simple3',
                    'name' => 'Simple 3',
                    'price' => 58.99,
                ],
                'categories' => [
                    'Category 3'
                ],
                'images' => [
                    'base_image' => '/0/1/01aaaa.png',
                    'small_image' => '/0/1/01bbbb.png',
                ]
            ],
        ],
        'configurable' => [
            'configurable' => [
                'general' => [
                    'sku' => 'configurable',
                    'name' => 'Configurable Product'
                ],
                'categories' => [
                    'Default Category'
                ],
                'options' => [
                    'simple_10',
                    'simple_20'
                ],
                'images' => [
                    '/m/a/magento_image_configurable.jpg'
                ]
            ],
            'configurable_12345' => [
                'general' => [
                    'sku' => 'configurable_12345',
                    'name' => 'Configurable Product New Name'
                ],
                'categories' => [
                    'Category 1',
                    'Category 2'
                ],
                'options' => [
                    'simple_30',
                    'simple_40'
                ],
                'images' => [
                    'base_image' => '/0/1/01aaaa.png',
                    'small_image' => '/0/1/01bbbb.png',
                    'thumbnail_image' => '/0/1/01cccc.png'
                ]
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->pathToImportFiles = str_replace(BP, '', __DIR__) . '/../_files/';

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $this->categoryRepository = $this->objectManager->create(\Magento\Catalog\Model\CategoryRepository::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store general/file/bunch_size 1
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoDataFixture multipleConfigurableProductsFixture
     */
    public function testItRollbacksProductsCorrectly()
    {
        $importCommand = $this->objectManager->create(\MageSuite\Importer\Command\Import\Import::class);
        $importCommand->execute([
            'source_path' => $this->pathToImportFiles . 'import.json',
            'behavior' => \MageSuite\Importer\Model\Import\Product::BEHAVIOR_UPDATE,
            'images_directory_path' => __DIR__ . '/../_files/images'
        ]);

        foreach ($this->expectedValues['simple'] as $productSku => $expectedData) {
            $this->checkProductRelatedData($productSku, $expectedData);
        }

        foreach ($this->expectedValues['configurable'] as $productSku => $expectedData) {
            $this->checkProductRelatedData($productSku, $expectedData);
            $this->checkChildrenRelatedData($productSku, $expectedData);
        }
    }

    protected function checkProductRelatedData($sku, $expectedData)
    {
        //Check basic data
        $product = $this->productRepository->get($sku);
        foreach ($expectedData['general'] as $key => $value) {
            $this->assertEquals($value, $product->getData($key));
        }

        //Check categories
        $categoryIds = $product->getCategoryIds();
        $this->assertEquals(count($expectedData['categories']), count($categoryIds));
        foreach ($categoryIds as $key => $categoryId) {
            $category = $this->categoryRepository->get($categoryId);
            $this->assertEquals($expectedData['categories'][$key], $category->getName());
        }

        //Check media gallery
        foreach ($expectedData['images'] as $imagePath) {
            $this->assertTrue($this->isImageInGallery($product, $imagePath));
        }
    }

    protected function checkChildrenRelatedData($sku, $expectedData)
    {
        $product = $this->productRepository->get($sku);
        $options = $product->getTypeInstance()->getUsedProducts($product);
        $this->assertEquals(count($expectedData['options']), count($options));

        foreach ($options as $key => $option) {
            $this->assertEquals($expectedData['options'][$key], $option->getSku());
        }
    }

    private function isImageInGallery(\Magento\Catalog\Model\Product $product, $imagePath)
    {
        $mediaGallery = $product->getData('media_gallery');

        foreach ($mediaGallery['images'] as $image) {
            if ($image['file'] == $imagePath) {
                return true;
            }
        }

        return false;
    }

    public static function multipleConfigurableProductsFixture()
    {
        require __DIR__.'/../_files/multiple_configurable_products.php';
    }

    public static function multipleConfigurableProductsFixtureRollback()
    {
        require __DIR__.'/../_files/multiple_configurable_products_rollback.php';
    }
}
