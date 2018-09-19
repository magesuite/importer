<?php

namespace MageSuite\Importer\Console\Command;

class Dispatcher extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \MageSuite\Importer\Services\Command\Dispatcher
     */
    private $commandDispatcher;

    /**
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \MageSuite\Importer\Services\Command\Dispatcher $commandDispatcher
    )
    {
        parent::__construct();

        $this->state = $state;
        $this->commandDispatcher = $commandDispatcher;
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
    )
    {
        $this->state->setAreaCode('frontend');

        while(true) {
            $this->commandDispatcher->dispatch();
            sleep(5);
        }
    }

}
