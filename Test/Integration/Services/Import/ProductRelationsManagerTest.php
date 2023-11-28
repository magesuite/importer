<?php

declare(strict_types=1);

namespace MageSuite\Importer\Test\Integration\Services\Import;

class ProductRelationsManagerTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\Importer\Services\Import\ProductRelationsManager $relationsManager = null;

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->relationsManager = $objectManager->create(\MageSuite\Importer\Services\Import\ProductRelationsManager::class);
    }

    /**
     * @dataProvider importRequest
     */
    public function testItProperlyDetectsChangedImages($importRequest, $expectedResult)
    {
        $actualResult = $this->relationsManager->getProductImagesChanges($importRequest);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function importRequest(): array
    {
        return [
            [
                [
                    'sku' => '12-3456',
                    'price' => 21.37,
                    'base_image' => 'test',
                ],
                [
                    'base_image' => 'test',
                ],
            ],
            [
                [
                    'sku' => '12-3456',
                    'price' => 21.37,
                    'base_image' => 'test',
                    'small_image' => 'test',
                ],
                [
                    'base_image' => 'test',
                    'small_image' => 'test',
                ],
            ],
            [
                [
                    'sku' => '12-3456',
                    'price' => 21.37,
                    'base_image' => 'test',
                    'small_image' => 'test',
                    'thumbnail' => 'test',
                ],
                [
                    'base_image' => 'test',
                    'small_image' => 'test',
                    'thumbnail' => 'test',
                ],
            ],
            [
                [
                    'sku' => '12-3456',
                    'price' => 21.37,
                    'base_image' => 'test',
                    'small_image' => 'test',
                    'thumbnail_image' => 'test',
                ],
                [
                    'base_image' => 'test',
                    'small_image' => 'test',
                    'thumbnail_image' => 'test',
                ],
            ],
            [
                [
                    'sku' => '12-3456',
                    'price' => 21.37,
                ],
                [],
            ],
        ];
    }
}
