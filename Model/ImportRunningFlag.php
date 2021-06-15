<?php

namespace MageSuite\Importer\Model;

class ImportRunningFlag
{
    protected $isRunning = false;

    public function isImportRunning()
    {
        return $this->isRunning;
    }

    public function setIsRunning(bool $value) {
        $this->isRunning = $value;
    }
}
