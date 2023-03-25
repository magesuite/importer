<?php

namespace MageSuite\Importer\Api;

interface ImportRunnerState
{
    /**
     * Returns whether there is an import scheduled thats needs runner to be executed
     * @return boolean
     */
    public function isImportRunnerNeeded();
}
