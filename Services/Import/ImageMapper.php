<?php

namespace MageSuite\Importer\Services\Import;

class ImageMapper
{
    protected \Magento\Framework\App\Filesystem\DirectoryList $directoryList;

    protected $imageTypes = [
        'base_image' => ['path' => '/', 'suffix' => ''],
        'small_image' => ['path' => '/', 'suffix' => '_small'],
        'thumbnail_image' => ['path' => '/', 'suffix' => '_thumbnail'],
        'additional_images' => ['path' => '/', 'suffix' => '_additional']
    ];

    protected $imageDirectory;
    protected $useBaseImageAsDefault;

    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->directoryList = $directoryList;
        $this->imageDirectory = $this->getImageDirectory();
        $this->useBaseImageAsDefault = $this->useBaseImageAsDefault();
    }

    protected function useBaseImageAsDefault()
    {
        return true;
    }

    public function getImageDirectory()
    {
        return $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR
            . 'importexport' . DIRECTORY_SEPARATOR
            . 'media' . DIRECTORY_SEPARATOR
            . 'product' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get image array for a given sku
     */
    public function getImagesByProductSku($productSku, $imageDirectory = null)
    {
        if (!$imageDirectory) {
            $imageDirectory = $this->imageDirectory;
        }
        $imageDirectory = rtrim($imageDirectory, '/');

        $images = [];
        foreach ($this->imageTypes as $type => $typeInfo) {
            if ($type == 'additional_images') {
                $files = glob($imageDirectory . $typeInfo['path'] . $productSku . $typeInfo['suffix'] . '_*.*');
                if (count($files)) {
                    foreach ($files as $file) {
                        $images[$type][] = str_replace($imageDirectory . '/', '', $file);
                    }
                    $images[$type] = implode(',', $images[$type]);
                }
            } else {
                $files = glob($imageDirectory . $typeInfo['path'] . $productSku . $typeInfo['suffix'] . '.*');
                if (isset($files[0])) {
                    $images[$type] = str_replace($imageDirectory . '/', '', $files[0]);
                }
            }
        }

        if ($this->useBaseImageAsDefault) {
            if (isset($images['base_image'])) {
                $images['small_image'] = isset($images['small_image']) ? $images['small_image'] : $images['base_image'];
                $images['thumbnail_image'] = isset($images['thumbnail_image']) ? $images['thumbnail_image'] : $images['base_image'];
            }
        }

        return $images;
    }
}
