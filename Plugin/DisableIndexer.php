<?php

namespace MageSuite\Importer\Plugin;

class DisableIndexer
{
    protected \MageSuite\Importer\Helper\Config $configuration;

    public function __construct(
        \MageSuite\Importer\Helper\Config $configuration
    ) {
        $this->configuration = $configuration;
    }

    public function aroundUpdateMview(\Magento\Indexer\Model\Processor $subject, callable $proceed)
    {
        if(!$this->configuration->isIndexerEnabled()) {
            throw new \Exception("Indexers are disabled");
        }

        return $proceed();
    }

    public function aroundReindexAllInvalid(\Magento\Indexer\Model\Processor $subject, callable $proceed)
    {
        if(!$this->configuration->isIndexerEnabled()) {
            throw new \Exception("Indexers are disabled");
        }

        return $proceed();
    }
}
