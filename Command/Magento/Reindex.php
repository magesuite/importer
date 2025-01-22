<?php

declare(strict_types=1);

namespace MageSuite\Importer\Command\Magento;

class Reindex implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Indexer\Model\IndexerFactory $indexerFactory;
    protected \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory;
    protected \MageSuite\Importer\Model\Command\OutputFactory $outputFactory;

    public function __construct(
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        \MageSuite\Importer\Model\Command\OutputFactory $outputFactory,
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->indexerCollectionFactory = $indexerCollectionFactory;
        $this->outputFactory = $outputFactory;
    }

    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        $indexes = $configuration['indexes'] ?? $this->getAllAvailableIndexes();

        $message = '';

        foreach ($indexes as $indexId) {
            $indexer = $this->indexerFactory->create();

            $indexer->load($indexId);

            $startTime = microtime(true);

            $indexer->getState()->setStatus(\Magento\Framework\Indexer\StateInterface::STATUS_VALID);
            $indexer->getState()->save();

            $indexer->reindexAll();

            $elapsed = microtime(true) - $startTime;

            $message .= $indexer->getTitle() . ' index has been rebuilt successfully in ' . gmdate('H:i:s', (int)ceil($elapsed)) . PHP_EOL;
        }

        return $this->outputFactory->create()->setMessage($message);
    }

    /**
     * @return string[]
     */
    protected function getAllAvailableIndexes(): array
    {
        $collection = $this->indexerCollectionFactory->create();

        return $collection->getAllIds();
    }
}
