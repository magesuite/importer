<?php

namespace MageSuite\Importer\Services\Command;

class Dispatcher
{
    const STEP_COMMAND = BP . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento importer:import:run_step %s %s &';

    /**
     * @var \MageSuite\Importer\Api\ImportRepositoryInterface
     */
    private $importRepository;

    private $configuration;

    private $steps;
    /**
     * @var \Magento\Framework\App\Shell
     */
    private $shell;

    public function __construct(
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository,
        \Magento\Framework\App\Shell $shell
    )
    {
        $this->importRepository = $importRepository;
        $this->shell = $shell;
    }

    public function dispatch() {
        $import = $this->importRepository->getActiveImport();

        if($import->getId() == 0) {
            return;
        }

        $importId = $import->getId();

        $this->configuration = $this->importRepository->getConfigurationById($import->getImportIdentifier());
        $this->steps = $this->importRepository->getStepsByImportId($importId);

        $stepsToRun = [];

        foreach($this->steps as $step) {
            if($this->canRunStep($step)) {
                $stepsToRun[] = $step;
            }
        }

        $this->runSteps($stepsToRun, $importId);
    }

    private function canRunStep($step)
    {
        if($step->getStatus() != \MageSuite\Importer\Model\ImportStep::STATUS_PENDING) {
            return false;
        }

        $stepDefinition = $this->configuration['steps'][$step->getIdentifier()];

        if(!isset($stepDefinition['depends']) OR empty($stepDefinition['depends'])) {
            return true;
        }

        $dependendStepsIdentifiers = explode(',', $stepDefinition['depends']);

        return $this->allDependendStepsHaveDoneStatus($dependendStepsIdentifiers);
    }

    private function allDependendStepsHaveDoneStatus($dependendStepsIdentifiers)
    {
        foreach($dependendStepsIdentifiers as $dependencyIdentifier) {
            foreach($this->steps as $step) {
                if($step->getIdentifier() == $dependencyIdentifier AND $step->getStatus() != \MageSuite\Importer\Model\ImportStep::STATUS_DONE) {
                    return false;
                }
            }
        }

        return true;
    }

    private function runSteps($stepsToRun, $importId)
    {
        if(empty($stepsToRun)) {
            return;
        }

        foreach($stepsToRun as $step) {
            $this->shell->execute(self::STEP_COMMAND, [$importId, $step->getIdentifier()]);
        }
    }
}