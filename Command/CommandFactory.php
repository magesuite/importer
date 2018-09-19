<?php

namespace MageSuite\Importer\Command;

interface CommandFactory
{
    /**
     * Creates command class based on it's type
     * @param $type
     * @return Command
     */
    public function create($type);
}