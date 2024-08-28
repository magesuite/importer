<?php

namespace MageSuite\Importer\Observer;

class BeginTransactionForProductBunch implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\Importer\Helper\Config $config;

    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    public function __construct(
        \MageSuite\Importer\Helper\Config $config,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        EndTransactionForProductBunch::$bunch = $observer->getBunch();

        if ($this->config->shouldUseTransactions()) {
            $connection = $this->resourceConnection->getConnection();
            $connection->beginTransaction();
        }
    }
}
