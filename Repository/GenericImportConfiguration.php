<?php

namespace MageSuite\Importer\Repository;

class GenericImportConfiguration implements ImportConfiguration
{
    public function __construct(
        protected \Magento\Framework\Filesystem\DriverPool $driverPool,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {}

    public function getById($id)
    {
        $jsonConfiguration = $this->driverPool
            ->getDriver(\Magento\Framework\Filesystem\DriverPool::FILE)
            ->fileGetContents(BP . DIRECTORY_SEPARATOR . 'import.json');

        return $this->serializer->unserialize($jsonConfiguration);
    }
}
