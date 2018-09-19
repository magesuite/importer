<?php

namespace MageSuite\Importer\Services\Import;

class ImageManager
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    protected $uploadedImages = null;

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    public function wasImagePreviouslyUploaded($path, $size)
    {
        return isset($this->getUploadedImages()[$path]) and $this->getUploadedImages()[$path] == $size;
    }

    public function insertImageMetadata($path, $size)
    {
        $this->connection->insertOnDuplicate('images_metadata', ['path' => $path, 'size' => $size], ['size']);

        $this->uploadedImages[$path] = $size;
    }

    public function resetUploadedImagesData() {
        $this->uploadedImages = null;
    }

    protected function getUploadedImages()
    {
        if ($this->uploadedImages == null) {
            $select = $this->connection->select()->from('images_metadata', ['path', 'size']);

            $this->uploadedImages = $this->connection->fetchPairs($select);
        }

        return $this->uploadedImages;
    }
}
