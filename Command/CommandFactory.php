<?php

namespace MageSuite\Importer\Command;

// phpcs:disable Magento2.NamingConvention.InterfaceName.WrongInterfaceName
interface CommandFactory
{
    /**
     * Creates command class based on it's type
     * @param $type
     * @return Command
     */
    public function create($type);
}
