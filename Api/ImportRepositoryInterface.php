<?php

namespace MageSuite\Importer\Api;

interface ImportRepositoryInterface
{
    public function getActiveImport();

    public function getConfigurationById($id);

    public function getById($id);

    public function save(\MageSuite\Importer\Model\Import $import);

    public function getStepsByImportId($id);

    public function saveStep(\MageSuite\Importer\Model\ImportStep $step);
}
