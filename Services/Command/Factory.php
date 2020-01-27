<?php

namespace MageSuite\Importer\Services\Command;

class Factory implements \MageSuite\Importer\Command\CommandFactory
{
    /**
     * @var array
     */
    protected $stepTypeMap;

    public function __construct(array $stepTypeMap)
    {
        $this->stepTypeMap = $stepTypeMap;
    }

    /**
     * Returns command class based on it's type
     * @param $type
     * @return \MageSuite\Importer\Command\Command
     */
    public function create($type)
    {
        if (!isset($this->stepTypeMap[$type])) {
            return null;
        }

        return $this->stepTypeMap[$type];
    }
}