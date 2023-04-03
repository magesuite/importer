<?php

namespace MageSuite\Importer\Model\Import;

class Product
{
    public const BEHAVIOR_SYNC = 'sync';
    public const BEHAVIOR_UPDATE = 'update';
    const IMPORT_FILE_TYPE = 'file';
    const IMPORT_DATA_TYPE = 'productData';
    const EXCLUDE_EXCEPTION_PROPERTIES = [
        'trace'
    ];

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
        $this->processImport($productData, self::IMPORT_DATA_TYPE, $behavior);
    }

    public function importFromFile($filePath, $behavior = self::BEHAVIOR_UPDATE)
    {
        return $this->processImport($filePath, self::IMPORT_FILE_TYPE, $behavior);
    }

    public function processImport($productData, $source, $behavior)
    {
        try {
            if ($source === self::IMPORT_FILE_TYPE) {
                $this->fileImporter->setImportAdapterFactory($this->fileAdapterFactory);
                $returnValue = $this->fileImporter->processImport($productData);
            } else if ($source === self::IMPORT_DATA_TYPE) {
                $this->importer->setImportAdapterFactory($this->nestedArrayAdapterFactory);
                $returnValue = $this->importer->processImport($productData);
            }
        } catch (\Exception $e) {
            $this->processException($e);
        }

        $this->executeBehaviorSpecificTasks($behavior);

        return $returnValue;
    }

    protected function processException($e)
    {
        $exceptionProperties = [];
        $reflectionClass = new \ReflectionClass($e);
        $classProperties = $reflectionClass->getProperties();

        while ($this->connection->getTransactionLevel() > 0) {
            $this->connection->rollBack();
        }

        foreach ($classProperties as $property) {
            if (in_array($property->name, self::EXCLUDE_EXCEPTION_PROPERTIES)) {
                continue;
            }
            $p = $reflectionClass->getProperty($property->name);
            $p->setAccessible(true);
            $propertyValue = $p->getValue($e);
            if (!is_null($propertyValue)) {
                $exceptionProperties[] = [
                    'name' => $property->name,
                    'value' => $p->getValue($e),
                ];
            }
        }

        $exceptionMessage = implode(' | ', array_map(function ($entry) {
            return $entry['name'] . ': ' . (is_array($entry['value']) ? var_export($entry['value'], true) : $entry['value']);
        }, $exceptionProperties));

        throw new \Exception($exceptionMessage);
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
