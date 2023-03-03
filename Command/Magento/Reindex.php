<?php

namespace MageSuite\Importer\Command\Magento;

class Reindex implements \MageSuite\Importer\Command\Command
{
    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $indexerCollectionFactory;

    public function __construct(
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->indexerCollectionFactory = $indexerCollectionFactory;
    }

    public function execute($configuration)
    {
        $indexes = isset($configuration['indexes']) ? $configuration['indexes'] : $this->getAllAvailableIndexes();

        $output = '';

        foreach ($indexes as $indexId) {
            $indexer = $this->indexerFactory->create();

            $indexer->load($indexId);

            $startTime = microtime(true);

            $indexer->getState()->setStatus(\Magento\Framework\Indexer\StateInterface::STATUS_VALID);
            $indexer->getState()->save();

            $indexer->reindexAll();

            $elapsed = microtime(true) - $startTime;

            $output .= $indexer->getTitle() . ' index has been rebuilt successfully in ' . gmdate('H:i:s', ceil($elapsed)) . PHP_EOL;
        }

        return $output;
    }

    protected function getAllAvailableIndexes()
    {
        $collection = $this->indexerCollectionFactory->create();

        return $collection->getAllIds();
    }
}
