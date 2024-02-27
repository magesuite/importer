<?php

namespace MageSuite\Importer\Console\Command;

class Dispatcher extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \MageSuite\Importer\Services\Command\DispatcherFactory
     */
    protected $commandDispatcherFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \MageSuite\Importer\Services\Command\DispatcherFactory $commandDispatcherFactory
    ) {
        parent::__construct();

        $this->state = $state;
        $this->commandDispatcherFactory = $commandDispatcherFactory;
    }

    protected function configure()
    {
        $this
            ->setName('importer:import:dispatcher')
            ->setDescription('Dispatch import commands');
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        $commandDispatcher = $this->commandDispatcherFactory->create();

        while (true) {
            $commandDispatcher->dispatch();
            sleep(5); // phpcs:ignore
        }
    }
}
