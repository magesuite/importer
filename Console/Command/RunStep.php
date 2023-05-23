<?php

namespace MageSuite\Importer\Console\Command;

class RunStep extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \MageSuite\Importer\Services\Command\RunnerFactory
     */
    protected $commandRunnerFactory;

    /**
     * @var \MageSuite\Importer\Api\ImportRepositoryInterfaceFactory
     */
    protected $importRepositoryFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \MageSuite\Importer\Services\Command\RunnerFactory $commandRunnerFactory,
        \MageSuite\Importer\Api\ImportRepositoryInterfaceFactory $importRepositoryFactory
    ) {
        parent::__construct();

        $this->state = $state;
        $this->commandRunnerFactory = $commandRunnerFactory;
        $this->importRepositoryFactory = $importRepositoryFactory;
    }

    protected function configure()
    {
        $this->addArgument(
            'import_id',
            \Symfony\Component\Console\Input\InputArgument::REQUIRED,
            'Id of import'
        );

        $this->addArgument(
            'step_identifier',
            \Symfony\Component\Console\Input\InputArgument::REQUIRED,
            'Identifier of step'
        );

        $this
            ->setName('importer:import:run_step')
            ->setDescription('Run step form import with specified id');
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        $importId = $input->getArgument('import_id');
        $importIdentifier = $this->getImportIdentifier($importId);
        $stepIdentifier = $input->getArgument('step_identifier');

        $commandRunnerFactory = $this->commandRunnerFactory->create();
        $commandRunnerFactory->runCommand($importId, $importIdentifier, $stepIdentifier);

        return 0;
    }

    protected function getImportIdentifier($importId)
    {
        $importRepository = $this->importRepositoryFactory->create();
        $import = $importRepository->getById($importId);

        return $import->getImportIdentifier();
    }
}
