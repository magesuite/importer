<?php

namespace MageSuite\Importer\Services\Import;

class RunnerState implements \MageSuite\Importer\Api\ImportRunnerStateInterface
{
    /**
     * @var \MageSuite\Importer\Api\ImportRepositoryInterface
     */
    protected $importRepository;

    public function __construct(\MageSuite\Importer\Api\ImportRepositoryInterface $importRepository)
    {
        $this->importRepository = $importRepository;
    }

    /**
     * @inheritdoc
     * @return boolean
     */
    public function isImportRunnerNeeded()
    {
        $activeImport = $this->importRepository->getActiveImport();

        return $activeImport->getId() ? true : false;
    }
}
