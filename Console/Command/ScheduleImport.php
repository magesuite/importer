<?php

namespace MageSuite\Importer\Console\Command;

class ScheduleImport extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var \MageSuite\Importer\Services\Import\Scheduler
     */
    private $scheduler;

    /**
     * ImportFile constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\State $state,
        \MageSuite\Importer\Services\Import\Scheduler $scheduler
    )
    {

        parent::__construct();

        $this->objectManager = $objectManager;
        $this->state = $state;
        $this->scheduler = $scheduler;
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
    )
    {
        $this->state->setAreaCode('frontend');

        $importIdentifier = $input->getArgument('import_id');

        $this->scheduler->scheduleImport($importIdentifier);
    }

}
