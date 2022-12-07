<?php
declare(strict_types=1);

namespace MageSuite\Importer\Observer;

class RollbackTransactionOnError implements \Magento\Framework\Event\ObserverInterface
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

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        if (!$this->config->shouldUseTransactions()) {
            return;
        }
        
        $connection = $this->resourceConnection->getConnection();

        if ($connection->getTransactionLevel() > 0) {
            $connection->rollBack();
        }
    }
}
