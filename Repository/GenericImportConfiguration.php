<?php

namespace MageSuite\Importer\Repository;

class GenericImportConfiguration implements ImportConfiguration
{
    protected \Magento\Framework\Filesystem\DriverInterface $driver;

    public function __construct(\Magento\Framework\Filesystem\DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function getById($id)
    {
        $jsonConfiguration = $this->driver->fileGetContents(BP . DIRECTORY_SEPARATOR . 'import.json');

        return json_decode($jsonConfiguration, true);
    }
}
