<?php

namespace MageSuite\Importer\Command;

// phpcs:disable Magento2.NamingConvention.InterfaceName.WrongInterfaceName
interface Parser
{
    /**
     * Parses input files and outputs unified file
     * @param $configuration
     * @return mixed
     */
    public function parse($configuration);
}
