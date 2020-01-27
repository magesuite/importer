<?php

namespace MageSuite\Importer\Console\Command;

class ScheduleImport extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \MageSuite\Importer\Services\Import\SchedulerFactory
     */
    protected $schedulerFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \MageSuite\Importer\Services\Import\SchedulerFactory $schedulerFactory
    ) {

        parent::__construct();

        $this->state = $state;
        $this->schedulerFactory = $schedulerFactory;
    }

    protected function configure()
    {
        $this->addArgument(
            'import_id',
            \Symfony\Component\Console\Input\InputArgument::REQUIRED,
            'Identifier of import in configuration'
        );

        $this
            ->setName('importer:import:schedule')
            ->setDescription('Schedule import task');
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        $importIdentifier = $input->getArgument('import_id');

        $scheduler = $this->schedulerFactory->create();
        $scheduler->scheduleImport($importIdentifier);
    }
}
