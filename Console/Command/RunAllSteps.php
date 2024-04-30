<?php

declare(strict_types=1);

namespace MageSuite\Importer\Console\Command;

class RunAllSteps extends \Symfony\Component\Console\Command\Command
{
    protected \MageSuite\Importer\Services\Import\StepRunner $stepRunner;

    public function __construct(\MageSuite\Importer\Services\Import\StepRunner $stepRunner) {
        $this->stepRunner = $stepRunner;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'import_id',
            \Symfony\Component\Console\Input\InputArgument::REQUIRED,
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
            $importId = (int)$input->getArgument('import_id');
            $this->stepRunner->runAllSteps($importId, $output);
            return 0;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return 1;
        }
    }
}
