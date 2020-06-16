<?php

namespace MageSuite\Importer\Services\Import;

class FailedImportDetector
{
    const SECONDS_IN_HOUR = 3600;
    const ERROR_MESSAGE = 'Step took too long to execute';

    /**
     * @var \MageSuite\Importer\Model\Collections\ImportStepFactory
     */
    protected $importStepCollectionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \MageSuite\Importer\Helper\Config
     */
    protected $config;


    public function __construct(
        \MageSuite\Importer\Model\Collections\ImportStepFactory $importStepCollectionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \MageSuite\Importer\Helper\Config $config,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    )
    {
        $this->importStepCollectionFactory = $importStepCollectionFactory;
        $this->eventManager = $eventManager;
        $this->dateTime = $dateTime;
        $this->config = $config;
    }

    public function markFailedImports()
    {
        $runningSteps = $this->getRunningSteps();

        if(empty($runningSteps)) {
            return;
        }

        $thresholdInHours = $this->config->getFailedImportThreshold();

        foreach ($runningSteps as $step) {
            if (!$this->stepRunningTimeExceededThreshold($step, $thresholdInHours)) {
                continue;
            }

            $this->eventManager->dispatch('import_command_error', ['step' => $step, 'error' => __(self::ERROR_MESSAGE), 'was_final_attempt' => true, 'attempt' => 1]);
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

    protected function getRunningSteps() {
        /** @var \MageSuite\Importer\Model\Collections\ImportStep $collection */
        $collection = $this->importStepCollectionFactory->create();

        $collection->addFieldToFilter('status', ['eq' => \MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS]);

        return $collection->getItems();
    }
}