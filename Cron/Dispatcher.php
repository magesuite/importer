<?php

namespace MageSuite\Importer\Cron;

class Dispatcher
{
    /**
     * @var \MageSuite\Importer\Services\Command\DispatcherFactory
     */
    protected $commandDispatcherFactory;

    /**
     * @var \MageSuite\Importer\Helper\Config
     */
    protected $config;

    public function __construct(
        \MageSuite\Importer\Services\Command\DispatcherFactory $commandDispatcherFactory,
        \MageSuite\Importer\Helper\Config $config
    )
    {
        $this->commandDispatcherFactory = $commandDispatcherFactory;
        $this->config = $config;
    }

    public function execute()
    {
        if(!$this->config->shouldUseCronToRunSteps()) {
            return;
        }

        $commandDispatcher = $this->commandDispatcherFactory->create();
        $commandDispatcher->dispatch();
    }
}