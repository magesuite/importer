<?php

namespace MageSuite\Importer\Services\Import;

class FailedImportDetector
{
    public const SECONDS_IN_HOUR = 3600;

    protected \MageSuite\Importer\Model\Collections\ImportStepFactory $importStepCollectionFactory;
    protected \Magento\Framework\Event\ManagerInterface $eventManager;
    protected \Magento\Framework\Stdlib\DateTime\DateTime $dateTime;
    protected \MageSuite\Importer\Helper\Config $config;

    public function __construct(
        \MageSuite\Importer\Model\Collections\ImportStepFactory $importStepCollectionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \MageSuite\Importer\Helper\Config $config,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->importStepCollectionFactory = $importStepCollectionFactory;
        $this->eventManager = $eventManager;
        $this->dateTime = $dateTime;
        $this->config = $config;
    }

    public function markFailedImports()
    {
        $runningSteps = $this->getRunningSteps();

        if (empty($runningSteps)) {
            return;
        }

        $thresholdInHours = $this->config->getFailedImportThreshold();

        foreach ($runningSteps as $step) {
            if (!$this->stepRunningTimeExceededThreshold($step, $thresholdInHours)) {
                continue;
            }

            $this->eventManager->dispatch('import_command_error', [
                'step' => $step,
                'error' => __('Step took too long to execute'),
                'was_final_attempt' => true,
                'attempt' => 1]);
        }
    }

    protected function stepRunningTimeExceededThreshold($step, $thresholdInHours)
    {
        $startTime = strtotime($step->getStartedAt());

        if ($startTime + ($thresholdInHours * self::SECONDS_IN_HOUR) <= $this->dateTime->timestamp()) {
            return true;
        }

        return false;
    }

    protected function getRunningSteps()
    {
        $collection = $this->importStepCollectionFactory->create();
        $collection->addFieldToFilter('status', ['eq' => \MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS]);

        return $collection->getItems();
    }
}
