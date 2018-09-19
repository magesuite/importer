<?php

namespace MageSuite\Importer\Model;

class ImportStatus
{
    /**
     * @var \MageSuite\Importer\Repository\ImportRepository
     */
    private $importRepository;

    public function __construct(\MageSuite\Importer\Repository\ImportRepository $importRepository)
    {
        $this->importRepository = $importRepository;
    }

    public function getByImportId($importId) {
        $steps = $this->importRepository->getStepsByImportId($importId);

        if($this->atLeastOneStepHasStatus($steps, \MageSuite\Importer\Model\ImportStep::STATUS_ERROR)) {
            return ImportStep::STATUS_ERROR;
        }

        if($this->atLeastOneStepHasStatus($steps, \MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS)) {
            return ImportStep::STATUS_IN_PROGRESS;
        }

        if($this->atLeastOneStepHasStatus($steps, \MageSuite\Importer\Model\ImportStep::STATUS_PENDING)) {
            return ImportStep::STATUS_PENDING;
        }

        return ImportStep::STATUS_DONE;
    }

    private function atLeastOneStepHasStatus($steps, $status) {
        /** @var \MageSuite\Importer\Model\ImportStep $step */
        foreach($steps as $step) {
            if($step->getStatus() == $status) {
                return true;
            }
        }

        return false;
    }
}