<?php

namespace MageSuite\Importer\Command\Import;

class Import implements \MageSuite\Importer\Command\Command
{
    const DEFAULT_ALLOWED_ERRORS_COUNT = 10;

    protected $magentoBuiltInBehaviors = [
        'replace',
        'delete',
        'append',
        'add_update'
    ];

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
        $allowedErrorsCount = $this->getAllowedErrorsCount($configuration);

        if (in_array($behavior, $this->magentoBuiltInBehaviors)) {
            $this->importer->setBehavior($behavior);
        }

        $this->importer->setValidationStrategy($validationStrategy);
        $this->importer->setEntityCode($entityCode);
        $this->importer->setAllowedErrorsCount($allowedErrorsCount);

        if (isset($configuration['bunch_grouping_field'])) {
            $this->importer->setBunchGroupingField($configuration['bunch_grouping_field']);
        }

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
        if (isset($configuration['behavior']) && !empty($configuration['behavior'])) {
            return $configuration['behavior'];
        }

        return \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE;
    }

    protected function getEntityCode($configuration)
    {
        if (isset($configuration['entity_code']) && !empty($configuration['entity_code'])) {
            return $configuration['entity_code'];
        }

        return 'catalog_product';
    }

    protected function getAllowedErrorsCount($configuration)
    {
        if (isset($configuration['allowed_errors_count']) && is_numeric($configuration['allowed_errors_count'])) {
            return (int)$configuration['allowed_errors_count'];
        }

        return self::DEFAULT_ALLOWED_ERRORS_COUNT;
    }
}
