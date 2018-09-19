<?php

namespace MageSuite\Importer\Command;

interface Command
{
    /**
     * Executes specific import related command
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration);
}