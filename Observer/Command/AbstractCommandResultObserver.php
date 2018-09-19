<?php

namespace MageSuite\Importer\Observer\Command;

class AbstractCommandResultObserver
{
    /**
     * @var \MageSuite\Importer\Api\ImportRepositoryInterface
     */
    protected $importRepository;

    /**
     * @var \MageSuite\Importer\Model\ImportStatus
     */
    protected $importStatus;

    public function __construct(
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository,
        \MageSuite\Importer\Model\ImportStatus $importStatus
    ) {
        $this->importRepository = $importRepository;
        $this->importStatus = $importStatus;
    }

    protected function recalculateCurrentImportStatus($importId) {
        $status = $this->importStatus->getByImportId($importId);

        $import = $this->importRepository->getById($importId);
        $import->setStatus($status);

        $this->importRepository->save($import);
    }
}
