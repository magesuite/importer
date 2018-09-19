<?php

namespace MageSuite\Importer\Services\Import;

class MediaGalleryImagesManager
{
    private $keysContainingImageChanges = [
        'base_image',
        'small_image',
        'thumbnail_image',
        'additional_images'
    ];

    private $attributeCodesToImportArrayMappings = [
        'image' => 'base_image',
        'small_image' => 'small_image',
        'thumbnail' => 'thumbnail_image'
    ];

    private $attributesIdsToCodes;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;


    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection) {
        $this->connection = $resourceConnection->getConnection();

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
    private function getAllProductImages($productIds)
    {
        $productImages = [];

        $select = $this->connection->select()
            ->from(
                ['cpemgv' => 'catalog_product_entity_media_gallery_value'],
                ['*']
            )
            ->joinLeft(
                ['cpemg' => 'catalog_product_entity_media_gallery'],
                'cpemg.value_id = cpemgv.value_id',
                ['*']
            )
            ->where('entity_id IN (?)', $productIds);

        $currentProductImages = $this->connection->fetchAll($select);

        foreach ($currentProductImages as $image) {
            $productImages[$image['entity_id']][$image['value']] = $image['value_id'];
        }

        return $productImages;
    }

    private function getAttributesIdsToCodes()
    {
        $select = $this->connection->select()
            ->from(
                ['ea' => 'eav_attribute'],
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
    private function getExistingSpecialImages($productIds)
    {
        $select = $this->connection->select()
            ->from(
                ['cpev' => 'catalog_product_entity_varchar'],
                ['*']
            )
            ->where('attribute_id IN (?)', array_keys($this->attributesIdsToCodes))
            ->where('entity_id IN (?)', $productIds);

        $images = $this->connection->fetchAll($select);

        $specialImages = [];

        foreach ($images as $image) {
            $attributeCode = $this->attributesIdsToCodes[ $image['attribute_id'] ];
            $specialImageType = $this->attributeCodesToImportArrayMappings[ $attributeCode ];
            $productId = $image['entity_id'];

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
    private function getSpecialImagesToDelete(
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

    private function getAdditionalImagesToDelete($imagesChanges, $existingAdditionalImages)
    {
        $imagesIdsToDelete = [];

        foreach ($imagesChanges as $productId => $imagesTypes) {
            if (isset($existingAdditionalImages[$productId]) and array_key_exists('additional_images', $imagesChanges[$productId])) {
                $imagesIdsToDelete = array_merge_recursive($imagesIdsToDelete, array_values($existingAdditionalImages[$productId]));
            }
        }

        return $imagesIdsToDelete;
    }

    private function deleteSpecialImagesAttributes($imagesChanges)
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
                'catalog_product_entity_varchar',
                [
                    'entity_id IN (?)' => $productIds,
                    'attribute_id = ?' => array_search($importArrayToAttributeCodesMapping[$specialImageType], $this->attributesIdsToCodes)
                ]
            );
        }

    }

    private function deleteImagesFromDatabase($imagesIds)
    {
        $this->connection->delete(
            'catalog_product_entity_media_gallery',
            ['value_id IN (?)' => $imagesIds]
        );

        $this->connection->delete(
            'catalog_product_entity_media_gallery_value',
            ['value_id IN (?)' => $imagesIds]
        );
    }
}