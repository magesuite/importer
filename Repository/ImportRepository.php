<?php

namespace MageSuite\Importer\Repository;

class ImportRepository implements \MageSuite\Importer\Api\ImportRepositoryInterface
{
    /**
     * @var \MageSuite\Importer\Model\ResourceModel\Import
     */
    private $importResourceModel;

    /**
     * @var \MageSuite\Importer\Model\ImportFactory
     */
    private $importFactory;

    /**
     * @var \MageSuite\Importer\Model\Collections\ImportStepFactory
     */
    private $importStepCollectionFactory;

    /**
     * @var \MageSuite\Importer\Model\ResourceModel\ImportStep
     */
    private $importStepResourceModel;

    /**
     * @var \MageSuite\Importer\Model\Collections\ImportFactory
     */
    private $importCollectionFactory;

    /**
     * @var ImportConfiguration
     */
    private $importConfiguration;

    public function __construct(
        \MageSuite\Importer\Model\ResourceModel\Import $importResourceModel,
        \MageSuite\Importer\Model\ResourceModel\ImportStep $importStepResourceModel,
        \MageSuite\Importer\Model\ImportFactory $importFactory,
        \MageSuite\Importer\Model\Collections\ImportStepFactory $importStepCollectionFactory,
        \MageSuite\Importer\Model\Collections\ImportFactory $importCollectionFactory,
        ImportConfiguration $importConfiguration
    )
    {
        $this->importResourceModel = $importResourceModel;
        $this->importFactory = $importFactory;
        $this->importStepCollectionFactory = $importStepCollectionFactory;
        $this->importStepResourceModel = $importStepResourceModel;
        $this->importCollectionFactory = $importCollectionFactory;
        $this->importConfiguration = $importConfiguration;
    }

    public function getById($id)
    {
        $import = $this->importFactory->create();

        return $import->load($id);
    }

    public function getConfigurationById($id)
    {
        return $this->importConfiguration->getById($id);
    }

    public function getStepsByImportId($id)
    {
        $collection = $this->importStepCollectionFactory->create();

        return $collection->addFilter('import_id', $id)->getItems();
    }

    public function saveStep(\MageSuite\Importer\Model\ImportStep $step)
    {
        return $this->importStepResourceModel->save($step);
    }

    public function save(\MageSuite\Importer\Model\Import $import)
    {
        return $this->importResourceModel->save($import);
    }

    public function getActiveImport()
    {
        /** @var \MageSuite\Importer\Model\Collections\Import $collection */
        $collection = $this->importCollectionFactory->create();

        $collection->addFieldToFilter('status', ['in' => [
            \MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS,
            \MageSuite\Importer\Model\ImportStep::STATUS_PENDING
            ]
        ]);

        $collection->addOrder('import_id', 'ASC');

        return $collection->getFirstItem();
    }
}