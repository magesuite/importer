<?php

namespace MageSuite\Importer\Observer\Command;

class CommandErrorObserver extends AbstractCommandResultObserver  implements \Magento\Framework\Event\ObserverInterface
{
    const ERROR_MESSAGE = "%s\n Error at attempt #%s on %s: \n %s";

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    public function __construct(
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository,
        \MageSuite\Importer\Model\ImportStatus $importStatus,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    )
    {
        parent::__construct($importRepository, $importStatus);

        $this->dateTime = $dateTime;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \MageSuite\Importer\Model\ImportStep $step */
        $step = $observer->getData('step');
        $attempt = $observer->getData('attempt') ?? 1;
        $error = $observer->getData('error');
        $wasFinalAttempt = $observer->getData('was_final_attempt');

        $existingError = $step->getError();

        $currentTime = date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp());

        $newError = sprintf(
            self::ERROR_MESSAGE,
            $existingError,
            $attempt,
            $currentTime,
            $error
        );

        $step->setError($newError);

        if($wasFinalAttempt) {
            $step->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_ERROR);
            $step->setFinishedAt(time());
        } else {
            $step->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_PENDING);
            $step->setRetriesCount($step->getRetriesCount()+1);
        }

        $this->importRepository->saveStep($step);

        $this->recalculateCurrentImportStatus($step->getImportId());
    }
}