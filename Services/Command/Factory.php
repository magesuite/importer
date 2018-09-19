<?php

namespace MageSuite\Importer\Services\Command;

class Factory implements \MageSuite\Importer\Command\CommandFactory
{
    private $typesToClassMapping = [
        'download' => \MageSuite\Importer\Command\File\Download::class,
        'download_newest' => \MageSuite\Importer\Command\File\DownloadNewest::class,
        'sync' => \MageSuite\Importer\Command\File\Sync::class,
        'parse' => \MageSuite\Importer\Command\Import\Parse::class,
        'map_images' => \MageSuite\Importer\Command\Import\MapImages::class,
        'import' => \MageSuite\Importer\Command\Import\Import::class,
        'create_directories' => \MageSuite\Importer\Command\File\CreateDirectories::class,
        'disable_indexers' => \MageSuite\Importer\Command\Magento\DisableIndexers::class,
        'enable_indexers' => \MageSuite\Importer\Command\Magento\EnableIndexers::class,
        'copy' => \MageSuite\Importer\Command\File\Copy::class,
        'move' => \MageSuite\Importer\Command\File\Move::class,
        'delete' => \MageSuite\Importer\Command\File\Delete::class,
    ];
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates command class based on it's type
     * @param $type
     * @return \MageSuite\Importer\Command\Command
     */
    public function create($type)
    {
        if(isset($this->typesToClassMapping[$type])) {
            return $this->objectManager->create($this->typesToClassMapping[$type]);
        }

        return null;
    }
}