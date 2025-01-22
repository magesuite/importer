<?php

declare(strict_types=1);

namespace MageSuite\Importer\Command\Magento;

class CleanCache implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Framework\App\Cache\Manager $cacheManager;
    protected \MageSuite\Importer\Model\Command\OutputFactory $outputFactory;

    public function __construct(
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \MageSuite\Importer\Model\Command\OutputFactory $outputFactory,
    ) {
        $this->cacheManager = $cacheManager;
        $this->outputFactory = $outputFactory;
    }

    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        $types = $configuration['cache_types'] ?? $this->cacheManager->getAvailableTypes();

        $this->cacheManager->clean($types);

        return $this->outputFactory->create();
    }
}
