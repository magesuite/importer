<?php

namespace MageSuite\Importer\Test\Integration\Model\Import;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    const MAGENTO_IMAGE_URL_FORMAT = '/%s/%s/%s';
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\Importer\Model\Import\Product
     */
    protected $simpleProductImporter;

    /**
     * @var \MageSuite\Importer\Services\Import\ImageMapper
     */
    protected $imageMapper;

    /**
     * @var \MageSuite\Importer\Services\Import\ImageManager
     */
    protected $imageManager;

    protected $directoryWithImages;
    protected $mediaCatalogDirectory = BP . '/pub/media/catalog/product';

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->simpleProductImporter = $this->objectManager->get(\MageSuite\Importer\Model\Import\Product::class);
        $this->imageMapper = $this->objectManager->get(\MageSuite\Importer\Services\Import\ImageMapper::class);
        $this->imageManager = $this->objectManager->get(\MageSuite\Importer\Services\Import\ImageManager::class);

        $this->directoryWithImages = $this->getFilesDirectoryPathRelativeToMainDirectory();

        $this->simpleProductImporter->setImportImagesFileDir($this->directoryWithImages);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItImportsNewProductAndDeletesOldOnes()
    {
        $productSku = 'new_product';

        $productData = $this->getProductImportArray($productSku, [
            'categories' => 'Default Category/Gear,Default Category/Bags'
        ]);

        $this->simpleProductImporter->importProductsFromData($productData, \MageSuite\Importer\Model\Import\Product::BEHAVIOR_SYNC);

        $this->assertFalse($this->productIsInRepository('simple'), 'Product with sku simple should be deleted but is still in database');
        $this->assertFalse($this->productIsInRepository('simple2'), 'Product with sku simple2 should be deleted but is still in database');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testItReplacesCategories()
    {
        $productSku = 'simple';

        $productData = $this->getProductImportArray($productSku, [
            'categories' => 'Default Category/Gear,Default Category/Bags'
        ]);

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $this->assertCount(2, $product->getCategoryIds());
        $this->assertNotContains(2, $product->getCategoryIds());
        $this->assertNotContains(3, $product->getCategoryIds());
        $this->assertNotContains(4, $product->getCategoryIds());
        $this->assertNotContains(13, $product->getCategoryIds());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_related_multiple.php
     */
    public function testItReplacesRelatedProducts()
    {
        $productSku = 'simple_with_cross';

        $productData = $this->getProductImportArray($productSku, [
            'related_skus' => 'simple'
        ]);

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $this->assertEquals(['1'], $product->getRelatedProductIds());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_upsell.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     */
    public function testItReplacesUpsellProducts()
    {
        $productSku = 'simple_with_upsell';

        $productData = $this->getProductImportArray($productSku, [
            'upsell_skus' => 'simple2'
        ]);

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $this->assertEquals(['6'], $product->getUpSellProductIds());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_crosssell.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     */
    public function testItReplacesCrosssellProducts()
    {
        $productSku = 'simple_with_cross';

        $productData = $this->getProductImportArray($productSku, [
            'crosssell_skus' => 'simple2'
        ]);

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $this->assertEquals(['6'], $product->getCrossSellProductIds());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProductWithImage
     */
    public function testImageShouldBeRemovedBecauseItIsReplacedEverywhere()
    {
        $productSku = 'simple';

        $productData = $this->getProductImportArray($productSku, [
            'base_image' => 'magento_image_replaced.jpg',
            'small_image' => 'magento_image_replaced.jpg',
            'thumbnail_image' => 'magento_image_replaced.jpg'
        ]);

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $this->assertFalse($this->isImageInGallery($product, '/m/a/magento_image.jpg'));
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadProductWithImage
     */
    public function testSpecialImagesShouldBeEmpty()
    {
        $productSku = 'simple';

        $productData = $this->getProductImportArray($productSku, [
            'base_image' => '',
            'small_image' => '',
            'thumbnail_image' => ''
        ]);

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $this->assertEquals('', $product->getImage());
        $this->assertEquals('', $product->getSmallImage());
        $this->assertEquals('', $product->getThumbnailImage());

        $this->assertFalse($this->isImageInGallery($product, '/m/a/magento_image.jpg'));
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadProductWithImage
     */
    public function testImageShouldNotBeRemovedBecauseItIsStillAThumbnailImage()
    {
        $productSku = 'simple';

        $productData = $this->getProductImportArray($productSku, [
            'base_image' => 'magento_image_replaced.jpg',
            'small_image' => 'magento_image_replaced.jpg'
        ]);

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $this->assertTrue($this->isImageInGallery($product, '/m/a/magento_image.jpg'));
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadProductWithAdditionalImages
     */
    public function testAllAdditionalImagesShouldBeRemovedButSpecialImagesShouldStay()
    {
        $productSku = 'simple';

        $productData = $this->getProductImportArray($productSku, [
            'additional_images' => ''
        ]);

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $this->assertFalse($this->isImageInGallery($product, '/m/a/magento_image_replaced.jpg'));
        $this->assertTrue($this->isImageInGallery($product, '/m/a/magento_image.jpg'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testImagesShouldBeAddedToProductWithoutImages()
    {
        $productSku = 'simple';

        $productData = $this->getProductImportArray($productSku, [
            'additional_images' => 'magento_image_new.jpg',
            'base_image' => 'magento_image.jpg',
            'small_image' => 'magento_image.jpg',
            'thumbnail_image' => 'magento_image.jpg'
        ]);

        $this->ensureImageDoesntExist('/m/a/magento_image_new.jpg');
        $this->ensureImageDoesntExist('/m/a/magento_image.jpg');

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $this->assertTrue($this->isImageInGallery($product, '/m/a/magento_image_new.jpg'));
        $this->assertTrue($this->isImageInGallery($product, '/m/a/magento_image.jpg'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testImagesShouldNotBeChanged()
    {
        $productSku = 'simple';

        $productData = $this->getProductImportArray($productSku, [
            'additional_images' => 'magento_image_new.jpg',
            'base_image' => 'magento_image.jpg',
        ]);

        $this->insertImageMetadata(['magento_image_new.jpg', 'magento_image.jpg']);

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $this->assertTrue($this->isImageInGallery($product, '/m/a/magento_image_new.jpg'));
        $this->assertTrue($this->isImageInGallery($product, '/m/a/magento_image.jpg'));
    }


    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testConfigurableVariationsShouldBeReplaced()
    {
        $productSku = 'configurable';

        $productData = [
            [
                'sku' => $productSku,
                'name' => 'Test Product',
                'price' => 22,
                'attribute_set_code' => 'Default',
                'product_type' => 'configurable',
                'product_websites' => 'base',
                'configurable_variation_labels' => 'Test',
                'configurable_variations' => [
                    ['sku' => 'simple_20', 'test_configurable' => 'Option 2']
                ]
            ]
        ];

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $configurableInstance = $product->getTypeInstance();

        $configurableVariations = $configurableInstance->getUsedProducts($product);

        $this->assertCount(1, $configurableVariations);
        $this->assertEquals(20, reset($configurableVariations)->getId());
    }

    public function testImportWithImagesFromDirectory()
    {
        $productSku = 'new_product';
        $additionalFields = [
            'categories' => 'Default Category/First'
        ];

        $imageData = $this->imageMapper->getImagesByProductSku($productSku, $this->directoryWithImages);
        $additionalFields = array_merge($additionalFields, $imageData);

        $this->ensureImageDoesntExist('/n/e/new_product.jpeg');
        $this->ensureImageDoesntExist('/n/e/new_product_small.jpeg');
        $this->ensureImageDoesntExist('/n/e/new_product_additional_0.jpeg');
        $this->ensureImageDoesntExist('/n/e/new_product_additional_1.jpeg');

        $productData = $this->getProductImportArray($productSku, $additionalFields);

        $this->simpleProductImporter->importProductsFromData($productData);

        $product = $this->getProductFromRepositoryBySku($productSku);

        $this->assertTrue($this->isImageInGallery($product, '/n/e/new_product.jpeg'));
        $this->assertTrue($this->isImageInGallery($product, '/n/e/new_product_small.jpeg'));
        $this->assertTrue($this->isImageInGallery($product, '/n/e/new_product_additional_0.jpeg'));
        $this->assertTrue($this->isImageInGallery($product, '/n/e/new_product_additional_1.jpeg'));
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    private function getProductFromRepositoryBySku($sku)
    {
        return $this->productRepository->get($sku);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $imagePath
     */
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

    /**
     * @return string
     */
    private function getFilesDirectoryPathRelativeToMainDirectory()
    {
        return realpath(__DIR__ . '/_files');
    }

    private function getProductImportArray($productSku, $additionalFields)
    {
        $productData = [
            'sku' => $productSku,
            'name' => 'Test Product',
            'url_key' => 'test-product',
            'price' => 22,
            'attribute_set_code' => 'Default',
            'product_type' => 'simple',
            'product_websites' => 'base'
        ];

        $productData = array_merge($productData, $additionalFields);

        return [$productData];
    }

    public static function loadProductWithImage()
    {
        require __DIR__ . '/_files/product_with_images.php';
    }

    public static function loadProductWithImageRollback()
    {
        require __DIR__ . '/_files/product_with_images_rollback.php';
    }

    public static function loadProductWithAdditionalImages()
    {
        require __DIR__ . '/_files/product_with_additional_images.php';
    }

    public static function loadProductWithAdditionalImagesRollback()
    {
        require __DIR__ . '/_files/product_with_images_rollback.php';
    }

    private function productIsInRepository($sku)
    {
        try {
            $this->productRepository->get($sku);

            return true;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    protected function ensureImageDoesntExist($imagePath)
    {
        $path = $this->mediaCatalogDirectory . $imagePath;

        if (!file_exists($path)) {
            return;
        }

        unlink($path);
    }

    protected function insertImageMetadata($images)
    {
        foreach ($images as $image) {
            $path = $this->directoryWithImages . '/' . $image;

            if (!file_exists($path)) {
                continue;
            }

            $size = filesize($path);
            $magentoImagePath = $this->getMagentoImagePath($image);

            $this->imageManager->insertImageMetadata($magentoImagePath, $size);
        }
    }

    protected function getMagentoImagePath($image)
    {
        return sprintf(self::MAGENTO_IMAGE_URL_FORMAT, $image[0], $image[1], $image);
    }
}
