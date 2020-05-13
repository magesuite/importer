<?php

namespace MageSuite\Importer\Command\Magento;

class CleanCache implements \MageSuite\Importer\Command\Command
{
    /*
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $cacheManager;

    public function __construct(\Magento\Framework\App\Cache\Manager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function execute($configuration)
    {
        $types = isset($configuration['cache_types']) ? $configuration['cache_types'] : $this->cacheManager->getAvailableTypes();

        $this->cacheManager->clean($types);
    }
}
