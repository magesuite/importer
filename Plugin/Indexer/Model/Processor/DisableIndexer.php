<?php

namespace MageSuite\Importer\Plugin\Indexer\Model\Processor;

class DisableIndexer
{
    protected \MageSuite\Importer\Helper\Config $config;

    public function __construct(\MageSuite\Importer\Helper\Config $config)
    {
        $this->config = $config;
    }

    public function isIndexerEnabled()
    {
        $indexerConfig = $this->config->getIndexerConfigFromDatabase();
        return empty($indexerConfig) ? false : $indexerConfig->getValue() === '1';
    }

    public function aroundUpdateMview(\Magento\Indexer\Model\Processor $subject, callable $proceed)
    {
        if (!$this->config->isIndexerEnabled()) {
            throw new \Exception("Indexers are disabled"); // phpcs:ignore
        }

        return $proceed();
    }

    public function aroundReindexAllInvalid(\Magento\Indexer\Model\Processor $subject, callable $proceed)
    {
        if (!$this->config->isIndexerEnabled()) {
            throw new \Exception("Indexers are disabled"); // phpcs:ignore
        }

        return $proceed();
    }
}
