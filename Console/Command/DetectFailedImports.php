<?php

namespace MageSuite\Importer\Console\Command;

class DetectFailedImports extends \Symfony\Component\Console\Command\Command
{
    protected \MageSuite\Importer\Services\Import\FailedImportDetectorFactory $failedImportDetectorFactory;
    protected \Magento\Framework\App\State $state;

    public function __construct(
        \Magento\Framework\App\State $state,
        \MageSuite\Importer\Services\Import\FailedImportDetectorFactory $failedImportDetectorFactory
    ) {
        parent::__construct();

        $this->failedImportDetectorFactory = $failedImportDetectorFactory;
        $this->state = $state;
    }

    protected function configure()
    {
        $this
            ->setName('importer:import:detect_failed_imports')
            ->setDescription('Detect imports that executed too long');
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        } catch (\Exception $e) {
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $failedImportDetector = $this->failedImportDetectorFactory->create();
        $failedImportDetector->markFailedImports();

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
