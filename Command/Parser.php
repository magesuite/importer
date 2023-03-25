<?php

namespace MageSuite\Importer\Command;

interface Parser
{
    /**
     * Parses input files and outputs unified file
     * @param $configuration
     * @return mixed
     */
    public function parse($configuration);
}
