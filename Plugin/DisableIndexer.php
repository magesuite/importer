<?php

namespace MageSuite\Importer\Plugin;

class DisableIndexer
{
    const INDEXER_ENABLED_XML_PATH = 'indexer/indexing/enabled';
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface)
    {
        $this->scopeConfigInterface = $scopeConfigInterface;
    }

    public function isIndexerEnabled() {
        return $this->scopeConfigInterface->getValue(self::INDEXER_ENABLED_XML_PATH) === '1';
    }

    public function aroundUpdateMview(\Magento\Indexer\Model\Processor $subject, callable $proceed) {
        if(!$this->isIndexerEnabled()) {
            throw new \Exception("Indexers are disabled");
        }

        return $proceed();
    }

    public function aroundReindexAllInvalid(\Magento\Indexer\Model\Processor $subject, callable $proceed) {
        if(!$this->isIndexerEnabled()) {
            throw new \Exception("Indexers are disabled");
        }

        return $proceed();
    }
}