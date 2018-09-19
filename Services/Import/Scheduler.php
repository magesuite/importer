<?php

namespace MageSuite\Importer\Services\Import;

class Scheduler
{
    /**
     * @var \MageSuite\Importer\Model\ImportFactory
     */
    private $importFactory;
    /**
     * @var \MageSuite\Importer\Model\ImportStepFactory
     */
    private $importStepFactory;
    /**
     * @var \MageSuite\Importer\Api\ImportRepositoryInterface
     */
    private $importRepository;

    public function __construct(
        \MageSuite\Importer\Model\ImportFactory $importFactory,
        \MageSuite\Importer\Model\ImportStepFactory $importStepFactory,
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository
    )
    {
        $this->importFactory = $importFactory;
        $this->importStepFactory = $importStepFactory;
        $this->importRepository = $importRepository;
    }

    public function scheduleImport($importIdentifier) {
        $configuration = $this->importRepository->getConfigurationById($importIdentifier);

        /** @var \MageSuite\Importer\Model\Import $import */
        $import = $this->importFactory->create();

        $import->setHash(uniqid());
        $import->setImportIdentifier($importIdentifier);
        $import->save();


        foreach($configuration['steps'] as $identifier => $step) {
            $importStep = $this->importStepFactory->create();
            $importStep->setImportId($import->getId());
            $importStep->setIdentifier($identifier);

            $importStep->save();
        }
    }
}