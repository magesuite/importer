<?php

namespace MageSuite\Importer\Command\Import;

// @phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
class MapImages implements \MageSuite\Importer\Command\Command
{
    protected \MageSuite\Importer\Services\Import\ImageMapper $imageMapper;
    protected \MageSuite\Importer\Model\Command\OutputFactory $outputFactory;
    protected \Magento\Framework\Filesystem\DriverInterface $driver;

    public function __construct(
        \MageSuite\Importer\Services\Import\ImageMapper $imageMapper,
        \MageSuite\Importer\Model\Command\OutputFactory $outputFactory,
        \Magento\Framework\Filesystem\DriverInterface $driver,
    ) {
        $this->imageMapper = $imageMapper;
        $this->outputFactory = $outputFactory;
        $this->driver = $driver;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        $sourceFileHandle = $this->driver->fileOpen(BP . DIRECTORY_SEPARATOR . $configuration['source_path'], 'r');
        $targetFileHandle = $this->driver->fileOpen(BP . DIRECTORY_SEPARATOR . $configuration['target_path'], 'w');
        $imagesDirectoryPath = BP . DIRECTORY_SEPARATOR . $configuration['images_directory_path'];

        if ($sourceFileHandle) {
            $firstLine = true;

            while (($line = fgets($sourceFileHandle)) !== false) {
                $row = json_decode($line, true);

                $row = array_merge(
                    $row,
                    $this->imageMapper->getImagesByProductSku($row['sku'], $imagesDirectoryPath)
                );

                $this->driver->fileWrite($targetFileHandle, !$firstLine ? PHP_EOL . json_encode($row) : json_encode($row));

                $firstLine = false;
            }
        }

        $this->driver->fileClose($sourceFileHandle);
        $this->driver->fileClose($targetFileHandle);

        return $this->outputFactory->create();
    }
}
