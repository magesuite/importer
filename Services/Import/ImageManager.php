<?php

namespace MageSuite\Importer\Services\Import;

class ImageManager
{
    const IMAGE_IDENTICAL = 1;
    const IMAGE_DOESNT_EXIST = 2;
    const IMAGE_DIFFERENT_SIZE = 3;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    protected $uploadedImages = null;

    /**
     * @var array
     */
    protected $imagesFileSizes = [];

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    public function wasImagePreviouslyUploaded($path, $size)
    {
        if (!isset($this->getUploadedImages()[$path])) {
            return self::IMAGE_DOESNT_EXIST;
        }

        if ($this->getUploadedImages()[$path] != $size) {
            return self::IMAGE_DIFFERENT_SIZE;
        }

        return self::IMAGE_IDENTICAL;
    }

    public function insertImageMetadata($path, $size)
    {
        $this->connection->insertOnDuplicate('images_metadata', ['path' => $path, 'size' => $size], ['size']);

        $this->uploadedImages[$path] = $size;
    }

    public function addImageFileSizeForUpdate($path, $size) {
        $this->imagesFileSizes[$path] = $size;
    }

    public function updateImageFileSizes() {
        if(empty($this->imagesFileSizes)) {
            return;
        }

        $table = $this->connection->getTableName('catalog_product_entity_media_gallery');

        foreach($this->imagesFileSizes as $path => $fileSize) {
            $this->connection->update(
                $table,
                ['file_size' => $fileSize],
                ['value = ?' => $path]
            );
        }

        $this->imagesFileSizes = [];
    }

    public function resetUploadedImagesData() {
        $this->uploadedImages = null;
    }

    protected function getUploadedImages()
    {
        if ($this->uploadedImages == null) {
            $select = $this->connection->select()->from($this->connection->getTableName('images_metadata'), ['path', 'size']);

            $this->uploadedImages = $this->connection->fetchPairs($select);
        }

        return $this->uploadedImages;
    }
}
