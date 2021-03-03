<?php

namespace MageSuite\Importer\Observer\Command;

class LogServerStatusBeforeCommandExecution extends AbstractCommandResultObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\ServerStatusLogger\Model\GenerateCurrentStatus
     */
    protected $generateServerStatus;

    public function __construct(
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository,
        \MageSuite\Importer\Model\ImportStatus $importStatus,
        \MageSuite\ServerStatusLogger\Model\GenerateCurrentStatus $generateServerStatus
    )
    {
        parent::__construct($importRepository, $importStatus);

        $this->generateServerStatus = $generateServerStatus;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \MageSuite\Importer\Model\ImportStep $step */
        $step = $observer->getData('step');
        $attempt = $observer->getData('attempt');

        $currentLog = json_decode($step->getServerStatusLog(), true);

        if(!is_array($currentLog)) {
            $currentLog = [];
        }

        $currentLog[$attempt] = $this->generateServerStatus->execute();

        $step->setServerStatusLog(json_encode($currentLog));

        $this->importRepository->saveStep($step);
    }
}