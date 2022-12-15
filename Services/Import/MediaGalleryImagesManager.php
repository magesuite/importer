<?php

namespace MageSuite\Importer\Services\Import;

class MediaGalleryImagesManager
{
    protected $keysContainingImageChanges = [
        'base_image',
        'small_image',
        'thumbnail_image',
        'additional_images'
    ];

    protected $attributeCodesToImportArrayMappings = [
        'image' => 'base_image',
        'small_image' => 'small_image',
        'thumbnail' => 'thumbnail_image'
    ];

    protected $attributesIdsToCodes;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    protected $productEntityLinkField;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->connection = $resourceConnection->getConnection();
        $this->metadataPool = $metadataPool;

        $this->attributesIdsToCodes = $this->getAttributesIdsToCodes();
    }


    public function getImportArrayKeysContainingImagesChanges() {
        return $this->keysContainingImageChanges;
    }

    public function deleteImages($imagesChanges) {
        $productsIds = array_keys($imagesChanges);

        $allProductImages = $this->getAllProductImages($productsIds);
        $existingSpecialImages = $this->getExistingSpecialImages($productsIds);
        $existingAdditionalImages = $this->getExistingAdditionalImages($allProductImages, $existingSpecialImages);

        $imagesIdsToDelete = $this->getSpecialImagesToDelete($imagesChanges, $existingSpecialImages, $allProductImages);
        $imagesIdsToDelete = array_merge_recursive($imagesIdsToDelete, $this->getAdditionalImagesToDelete($imagesChanges, $existingAdditionalImages));

        $this->deleteSpecialImagesAttributes($imagesChanges);
        $this->deleteImagesFromDatabase($imagesIdsToDelete);
    }

    /**
     * Returns all product images from media gallery
     * Keys are image paths, values are images ids.
     * Example output
     * [
     *      1 => ['/m/a/magento_image.jpg' => 3000]
     * ]
     * @param $productIds
     * @return array
     */
    protected function getAllProductImages($productIds)
    {
        $productImages = [];

        $select = $this->connection->select()
            ->from(
                ['cpemgv' => $this->connection->getTableName('catalog_product_entity_media_gallery_value')],
                ['*']
            )
            ->joinLeft(
                ['cpemg' => $this->connection->getTableName('catalog_product_entity_media_gallery')],
                'cpemg.value_id = cpemgv.value_id',
                ['*']
            )
            ->where($this->getProductEntityLinkCondition(), $productIds);

        $currentProductImages = $this->connection->fetchAll($select);

        foreach ($currentProductImages as $image) {
            $productImages[$image[$this->getProductEntityLinkField()]][$image['value']] = $image['value_id'];
        }

        return $productImages;
    }

    protected function getAttributesIdsToCodes()
    {
        $select = $this->connection->select()
            ->from(
                ['ea' => $this->connection->getTableName('eav_attribute')],
                ['attribute_id', 'attribute_code']
            )
            ->where('entity_type_id = ?', '4')
            ->where('attribute_code IN (?)', array_keys($this->attributeCodesToImportArrayMappings));

        return $this->connection->fetchPairs($select);
    }

    /**
     * Returns only special images (small, thumbnail, base)
     * Example output
     * [
     *      1 => ['base_image' => '/m/a/magento_image.jpg', 'thumbnail_image' => '/m/a/magento_thumbnail.jpg']
     * ]
     * Keys are product ids.
     * @param $productIds
     * @return array
     */
    protected function getExistingSpecialImages($productIds)
    {
        $select = $this->connection->select()
            ->from(
                ['cpev' => $this->connection->getTableName('catalog_product_entity_varchar')],
                ['*']
            )
            ->where('attribute_id IN (?)', array_keys($this->attributesIdsToCodes))
            ->where($this->getProductEntityLinkCondition(), $productIds);

        $images = $this->connection->fetchAll($select);

        $specialImages = [];

        foreach ($images as $image) {
            $attributeCode = $this->attributesIdsToCodes[ $image['attribute_id'] ];
            $specialImageType = $this->attributeCodesToImportArrayMappings[ $attributeCode ];
            $productId = $image[$this->getProductEntityLinkField()];

            $specialImages[$productId][$specialImageType] = $image['value'];
        }

        return $specialImages;
    }

    /**
     * Returns every image that is not special image
     * @param $allProductImages
     * @param $specialImages
     * @return array
     */
    public function getExistingAdditionalImages($allProductImages, $specialImages)  {
        $additionalImages = [];

        foreach ($allProductImages as $productId => $images) {
            foreach ($images as $imagePath => $imageId) {
                if (!isset($specialImages[$productId]) or !in_array($imagePath, $specialImages[$productId])) {
                    $additionalImages[$productId][$imagePath] = $imageId;
                }
            }
        }

        return $additionalImages;
    }

    /**
     * Applies all changes from import array to special images.
     * Replaced image is removed only if it is no longer needed in other special images types.
     * @param $imagesChanges
     * @param $existingSpecialImages
     * @param $productImagesIds
     * @return array
     */
    protected function getSpecialImagesToDelete(
        $imagesChanges,
        $existingSpecialImages,
        $productImagesIds
    )
    {
        $possibleImagesToDelete = [];

        foreach ($existingSpecialImages as $productId => $images) {
            foreach ($images as $specialImageType => $imagePath) {
                if (isset($imagesChanges[$productId][$specialImageType])) {
                    if(isset($productImagesIds[$productId][$imagePath])) {
                        $possibleImagesToDelete[$productId][$imagePath] = $productImagesIds[$productId][$imagePath];
                    }

                    $existingSpecialImages[$productId][$specialImageType] = $imagesChanges[$productId][$specialImageType];
                }
            }
        }

        $imagesIdsToDelete = [];

        foreach ($possibleImagesToDelete as $productId => $images) {
            foreach ($images as $imagePath => $imageId) {
                if (!in_array($imagePath, $existingSpecialImages[$productId])) {
                    $imagesIdsToDelete[] = $imageId;
                }
            }
        }

        return $imagesIdsToDelete;
    }

    protected function getAdditionalImagesToDelete($imagesChanges, $existingAdditionalImages)
    {
        $imagesIdsToDelete = [];

        foreach ($imagesChanges as $productId => $imagesTypes) {
            if (isset($existingAdditionalImages[$productId]) and array_key_exists('additional_images', $imagesChanges[$productId])) {
                $imagesIdsToDelete = array_merge_recursive($imagesIdsToDelete, array_values($existingAdditionalImages[$productId]));
            }
        }

        return $imagesIdsToDelete;
    }

    protected function deleteSpecialImagesAttributes($imagesChanges)
    {
        $importArrayToAttributeCodesMapping = array_flip($this->attributeCodesToImportArrayMappings);

        $attributesToDelete = [];

        foreach ($imagesChanges as $productId => $specialImagesTypes) {
            foreach ($specialImagesTypes as $specialImageType => $change) {
                $attributesToDelete[$specialImageType][] = $productId;
            }
        }

        foreach ($attributesToDelete as $specialImageType => $productIds) {
            if (!isset($importArrayToAttributeCodesMapping[$specialImageType])) {
                continue;
            }

            $this->connection->delete(
                $this->connection->getTableName('catalog_product_entity_varchar'),
                [
                    $this->getProductEntityLinkCondition() => $productIds,
                    'attribute_id = ?' => array_search($importArrayToAttributeCodesMapping[$specialImageType], $this->attributesIdsToCodes)
                ]
            );
        }

    }

    protected function deleteImagesFromDatabase($imagesIds)
    {
        $this->connection->delete(
            $this->connection->getTableName('catalog_product_entity_media_gallery'),
            ['value_id IN (?)' => $imagesIds]
        );

        $this->connection->delete(
            $this->connection->getTableName('catalog_product_entity_media_gallery_value'),
            ['value_id IN (?)' => $imagesIds]
        );
    }

    protected function getProductEntityLinkCondition()
    {
        return sprintf('%s IN (?)', $this->getProductEntityLinkField());
    }

    protected function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->metadataPool
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }
}
