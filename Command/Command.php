<?php

namespace MageSuite\Importer\Command;

// phpcs:disable Magento2.NamingConvention.InterfaceName.WrongInterfaceName
interface Command
{
    /**
     * Executes specific import related command
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration);
}
