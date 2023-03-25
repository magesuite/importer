<?php

namespace MageSuite\Importer\Model\Import;

class Product
{
    public const BEHAVIOR_SYNC = 'sync';
    public const BEHAVIOR_UPDATE = 'update';

    protected \FireGento\FastSimpleImport\Model\Adapters\NestedArrayAdapterFactory $nestedArrayAdapterFactory;
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected \MageSuite\Importer\Model\FileImporter $fileImporter;
    protected \MageSuite\Importer\Model\Import\Adapter\FileAdapterFactory $fileAdapterFactory;

    public function __construct(
        \FireGento\FastSimpleImport\Model\Importer $importer,
        \FireGento\FastSimpleImport\Model\Adapters\NestedArrayAdapterFactory $nestedArrayAdapterFactory,
        \MageSuite\Importer\Model\FileImporter $fileImporter,
        \MageSuite\Importer\Model\Import\Adapter\FileAdapterFactory $fileAdapterFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->connection = $resourceConnection->getConnection();
        $this->importer = $importer;
        $this->nestedArrayAdapterFactory = $nestedArrayAdapterFactory;
        $this->fileImporter = $fileImporter;
        $this->fileAdapterFactory = $fileAdapterFactory;
    }

    public function setImportImagesFileDir($directory)
    {
        $this->importer->setImportImagesFileDir($directory);
        $this->fileImporter->setImportImagesFileDir($directory);
    }

    public function setBehavior($behavior)
    {
        $this->importer->setBehavior($behavior);
        $this->fileImporter->setBehavior($behavior);
    }

    public function setEntityCode($entityCode)
    {
        $this->importer->setEntityCode($entityCode);
        $this->fileImporter->setEntityCode($entityCode);
    }

    public function setValidationStrategy($strategy)
    {
        $this->importer->setValidationStrategy($strategy);
        $this->fileImporter->setValidationStrategy($strategy);
    }

    public function importProductsFromData($productData, $behavior = self::BEHAVIOR_UPDATE)
    {
        $this->importer->setImportAdapterFactory($this->nestedArrayAdapterFactory);
        $this->importer->processImport($productData);

        $this->executeBehaviorSpecificTasks($behavior);
    }

    public function importFromFile($filePath, $behavior = self::BEHAVIOR_UPDATE)
    {
        $this->fileImporter->setImportAdapterFactory($this->fileAdapterFactory);
        $returnValue = $this->fileImporter->processImport($filePath);

        $this->executeBehaviorSpecificTasks($behavior);

        return $returnValue;
    }

    public function getLogTrace()
    {
        return $this->fileImporter->getLogTrace();
    }

    protected function executeBehaviorSpecificTasks($behavior)
    {
        if ($behavior == self::BEHAVIOR_SYNC) {
            $importedSkus = \MageSuite\Importer\Model\ImportedProductsAggregator::getSkus();
            $notImportedSkus = $this->getNotImportedSkus($importedSkus);

            $this->deleteProductsBySkus($notImportedSkus);
        }
    }

    protected function getNotImportedSkus($importedSkus)
    {
        $select = $this->connection->select()
            ->from(
                ['cpe' => $this->connection->getTableName('catalog_product_entity')],
                ['sku']
            )
            ->where('sku NOT IN (?)', $importedSkus);

        return $this->connection->fetchAll($select);
    }

    protected function deleteProductsBySkus($skusToDelete)
    {
        if (empty($skusToDelete)) {
            return;
        }

        $this->importer->setBehavior(\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE);
        $this->importer->setEntityCode('catalog_product');
        $this->importer->processImport($skusToDelete);
    }
}
