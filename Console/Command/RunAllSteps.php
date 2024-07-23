<?php

declare(strict_types=1);

namespace MageSuite\Importer\Console\Command;

class RunAllSteps extends \Symfony\Component\Console\Command\Command
{
    protected \MageSuite\Importer\Services\Import\StepRunner $stepRunner;
    protected \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository;

    public function __construct(
        \MageSuite\Importer\Services\Import\StepRunner $stepRunner,
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository
    ) {
        $this->stepRunner = $stepRunner;
        $this->importRepository = $importRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'import_id',
            \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
            'ID of the import'
        );

        $this
            ->setName('importer:import:run_all_steps')
            ->setDescription('Run every step of import with specified ID');
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ): int {
        try {
            $importId = $input->getArgument('import_id') ?? $this->importRepository->getActiveImport()->getId();

            if (!$importId) {
                $output->writeln('Nothing to import.');
            }

            $this->stepRunner->runAllSteps((int)$importId, $output);
            return 0;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return 1;
        }
    }
}
