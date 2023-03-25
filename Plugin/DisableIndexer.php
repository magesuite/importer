<?php

namespace MageSuite\Importer\Plugin;

class DisableIndexer
{
    const INDEXER_ENABLED_XML_SECTION = 'indexer/indexing';

    const INDEXER_ENABLED_XML_PATH = 'indexer/indexing/enabled';

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory
     */
    protected $configCollectionFactory;

    public function __construct(\Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configCollectionFactory)
    {
        $this->configCollectionFactory = $configCollectionFactory;
    }

    public function isIndexerEnabled()
    {
        $indexerConfig = $this->getIndexerConfigFromDatabase();
        return empty($indexerConfig) ? false : $indexerConfig->getValue() === '1';
    }

    public function aroundUpdateMview(\Magento\Indexer\Model\Processor $subject, callable $proceed)
    {
        if (!$this->isIndexerEnabled()) {
            throw new \Exception("Indexers are disabled");
        }

        return $proceed();
    }

    public function aroundReindexAllInvalid(\Magento\Indexer\Model\Processor $subject, callable $proceed)
    {
        if (!$this->isIndexerEnabled()) {
            throw new \Exception("Indexers are disabled");
        }

        return $proceed();
    }

    protected function getIndexerConfigFromDatabase()
    {
        $configCollection = $this->configCollectionFactory->create();
        $configCollection->addScopeFilter(
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0,
            self::INDEXER_ENABLED_XML_SECTION
        )->addFieldToFilter('path', ['eq' => self::INDEXER_ENABLED_XML_PATH]);

        return current($configCollection->getItems());
    }
}
