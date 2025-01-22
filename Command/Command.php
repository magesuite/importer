<?php

namespace MageSuite\Importer\Command;

// phpcs:disable Magento2.NamingConvention.InterfaceName.WrongInterfaceName
interface Command
{
    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output;
}
