<?php

namespace MageSuite\Importer\Services\Import;

class ImageManager
{
    public const IMAGE_IDENTICAL = 1;
    public const IMAGE_DOESNT_EXIST = 2;
    public const IMAGE_DIFFERENT_SIZE = 3;

    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected ?array $uploadedImages = null;
    protected array $imagesFileSizes = [];

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    public function wasImagePreviouslyUploaded(string $path, $size):int
    {
        if (!isset($this->getUploadedImages()[$path])) {
            return self::IMAGE_DOESNT_EXIST;
        }

        if ($this->getUploadedImages()[$path] != $size) {
            return self::IMAGE_DIFFERENT_SIZE;
        }

        return self::IMAGE_IDENTICAL;
    }

    public function updateMultipleImageMetadata(?array $imagesFileSizes = null):void
    {
        $imagesFileSizes = $imagesFileSizes ?? $this->imagesFileSizes;
        $imageMetadataToInsert = [];
        $imageMetadataToUpdate = [];

        foreach ($imagesFileSizes as $path => $fileSize) {
            $check = $this->wasImagePreviouslyUploaded($path, $fileSize);
            $this->uploadedImages[$path] = $fileSize;

            switch ($check) {
                case self::IMAGE_DIFFERENT_SIZE:
                    $imageMetadataToUpdate[$path] = $fileSize;
                    break;
                case self::IMAGE_DOESNT_EXIST:
                    $imageMetadataToInsert[] = ['size' => $fileSize, 'path' => $path];
                    break;
            }
        }

        if (!empty($imageMetadataToInsert)) {
            $this->connection->insertMultiple(
                $this->connection->getTableName('images_metadata'),
                $imageMetadataToInsert
            );
        }

        if (!empty($imageMetadataToUpdate)) {
            $conditions = [];
            foreach ($imageMetadataToUpdate as $path => $fileSize) {
                $case = $this->connection->quoteInto('?', $path);
                $result = $this->connection->quoteInto('?', $fileSize);
                $conditions[$case] = $result;
            }

            $value = $this->connection->getCaseSql('`path`', $conditions, '`size`');
            $where = ['`path` IN (?)' => array_keys($this->imagesFileSizes)];
            $this->connection->update($this->connection->getTableName('images_metadata'), ['size' => $value], $where);
        }
    }

    public function insertImageMetadata($path, $size):void
    {
        $this->connection->insertOnDuplicate('images_metadata', ['path' => $path, 'size' => $size], ['size']);
        $this->uploadedImages[$path] = $size;
    }

    public function addImageFileSizeForUpdate(string $path, $size):void
    {
        $this->imagesFileSizes[$path] = $size;
    }

    public function updateImageFileSizes():void
    {
        if (empty($this->imagesFileSizes)) {
            return;
        }

        $conditions = [];
        foreach ($this->imagesFileSizes as $path => $fileSize) {
            $case = $this->connection->quoteInto('?', $path);
            $result = $this->connection->quoteInto('?', $fileSize);
            $conditions[$case] = $result;
        }

        $value = $this->connection->getCaseSql('`value`', $conditions, '`file_size`');
        $this->connection->update(
            $this->connection->getTableName('catalog_product_entity_media_gallery'),
            ['file_size' => $value],
            ['`value` IN (?)' => array_keys($this->imagesFileSizes)]
        );

        $this->updateMultipleImageMetadata();
        $this->resetImagesFileSizesData();
    }

    protected function getUploadedImages():array
    {
        if ($this->uploadedImages === null) {
            $select = $this->connection->select()->from(
                $this->connection->getTableName('images_metadata'),
                ['path', 'size']
            );
            $this->uploadedImages = $this->connection->fetchPairs($select) ?? [];
        }

        return $this->uploadedImages;
    }

    public function resetUploadedImagesData():void
    {
        $this->uploadedImages = null;
    }

    public function resetImagesFileSizesData():void
    {
        $this->imagesFileSizes = [];
    }
}
