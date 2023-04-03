<?php

namespace MageSuite\Importer\Command\Import;

class Import implements \MageSuite\Importer\Command\Command
{
    protected $magentoBuiltInBehaviors = ['replace'];
    protected \MageSuite\Importer\Model\Import\Product $importer;

    public function __construct(\MageSuite\Importer\Model\Import\Product $importer)
    {
        $this->importer = $importer;
    }

    /**
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration)
    {
        $sourcePath = BP . DIRECTORY_SEPARATOR . $configuration['source_path'];

        if (isset($configuration['images_directory_path'])) {
            $imagesDirectoryPath = $configuration['images_directory_path'];
            $this->importer->setImportImagesFileDir($imagesDirectoryPath);
        }

        $validationStrategy = $this->getValidationStrategy($configuration);
        $behavior = $this->getBehavior($configuration);
        $entityCode = $this->getEntityCode($configuration);

        if (in_array($behavior, $this->magentoBuiltInBehaviors)) {
            $this->importer->setBehavior($behavior);
        }

        $this->importer->setValidationStrategy($validationStrategy);
        $this->importer->setEntityCode($entityCode);

        return $this->importer->importFromFile($sourcePath, $behavior);
    }

    /**
     * @param $configuration
     * @return string
     */
    protected function getValidationStrategy($configuration)
    {
        if (isset($configuration['validation_strategy']) && $configuration['validation_strategy'] == 'skip') {
            return \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS;
        }

        return \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR;
    }

    /**
     * @param $configuration
     * @return string
     */
    protected function getBehavior($configuration)
    {
        if (isset($configuration['behavior']) && $configuration['behavior'] == 'sync') {
            return \MageSuite\Importer\Model\Import\Product::BEHAVIOR_SYNC;
        } elseif (isset($configuration['behavior']) && $configuration['behavior'] == 'replace') {
            return \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE;
        }

        return \MageSuite\Importer\Model\Import\Product::BEHAVIOR_UPDATE;
    }

    protected function getEntityCode($configuration)
    {
        if (isset($configuration['entity_code']) && !empty($configuration['entity_code'])) {
            return $configuration['entity_code'];
        }

        return 'catalog_product';
    }
}
