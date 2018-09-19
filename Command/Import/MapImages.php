<?php

namespace MageSuite\Importer\Command\Import;

class MapImages implements \MageSuite\Importer\Command\Command
{
    /**
     * @var \MageSuite\Importer\Services\Import\ImageMapper
     */
    private $imageMapper;

    public function __construct(\MageSuite\Importer\Services\Import\ImageMapper $imageMapper)
    {
        $this->imageMapper = $imageMapper;
    }

    /**
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration)
    {
        $sourceFileHandle = fopen(BP . DIRECTORY_SEPARATOR . $configuration['source_path'], "r");
        $targetFileHandle = fopen(BP . DIRECTORY_SEPARATOR . $configuration['target_path'], "w");

        $imagesDirectoryPath = BP . DIRECTORY_SEPARATOR . $configuration['images_directory_path'];

        if ($sourceFileHandle) {
            $firstLine = true;

            while (($line = fgets($sourceFileHandle)) !== false) {
                $row = json_decode($line, true);

                $row = array_merge(
                    $row,
                    $this->imageMapper->getImagesByProductSku($row['sku'], $imagesDirectoryPath)
                );

                fwrite($targetFileHandle, !$firstLine ? PHP_EOL . json_encode($row) : json_encode($row));

                $firstLine = false;
            }
        }
    }
}