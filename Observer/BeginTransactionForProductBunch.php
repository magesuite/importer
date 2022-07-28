<?php

namespace MageSuite\Importer\Observer;

class BeginTransactionForProductBunch implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\Importer\Helper\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    public function __construct(
        \MageSuite\Importer\Helper\Config $config,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->shouldUseTransactions()) {
            $adapter = $observer->getData('adapter');
            EndTransactionForProductBunch::$errorAmount = count($adapter->getErrorAggregator()->getAllErrors());

            $connection = $this->resourceConnection->getConnection();
            $connection->beginTransaction();
        }
    }
}
