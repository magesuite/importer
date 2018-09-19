<?php

namespace MageSuite\Importer\Console\Command;

class RunStep extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var \MageSuite\Importer\Services\Command\Runner
     */
    private $commandRunner;
    /**
     * @var \MageSuite\Importer\Model\Import
     */
    private $import;
    /**
     * @var \MageSuite\Importer\Api\ImportRepositoryInterface
     */
    private $importRepository;

    /**
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \MageSuite\Importer\Services\Command\Runner $commandRunner,
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository
    )
    {
        parent::__construct();

        $this->state = $state;
        $this->commandRunner = $commandRunner;
        $this->importRepository = $importRepository;
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
    )
    {
        $this->state->setAreaCode('frontend');

        $importId = $input->getArgument('import_id');
        $importIdentifier = $this->getImportIdentifier($importId);
        $stepIdentifier = $input->getArgument('step_identifier');

        $this->commandRunner->runCommand($importId, $importIdentifier, $stepIdentifier);
    }

    protected function getImportIdentifier($importId) {
        $import = $this->importRepository->getById($importId);

        return $import->getImportIdentifier();
    }

}
