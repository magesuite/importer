<?php

namespace MageSuite\Importer\Observer\Command;

class CommandWarningObserver extends AbstractCommandResultObserver implements \Magento\Framework\Event\ObserverInterface
{
    public const WARNING_MESSAGE = "%s\n Warning at attempt #%s on %s: \n %s";

    protected \Magento\Framework\Stdlib\DateTime\DateTime $dateTime;

    public function __construct(
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository,
        \MageSuite\Importer\Model\ImportStatus $importStatus,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        parent::__construct($importRepository, $importStatus);

        $this->dateTime = $dateTime;
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        /** @var \MageSuite\Importer\Model\ImportStep $step */
        $step = $observer->getData('step');
        $attempt = $observer->getData('attempt') ?? 1;
        $warning = $observer->getData('warning');
        $wasFinalAttempt = $observer->getData('was_final_attempt');

        $existingError = $step->getError();

        $currentTime = date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp());

        $newError = sprintf(
            self::WARNING_MESSAGE,
            $existingError,
            $attempt,
            $currentTime,
            $warning
        );

        $step->setError($newError);

        if ($wasFinalAttempt) {
            $step->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_WARNING);
            $step->setFinishedAt(time());
        } else {
            $step->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_PENDING);
            $step->setRetriesCount($step->getRetriesCount()+1);
        }

        $this->importRepository->saveStep($step);

        $this->recalculateCurrentImportStatus($step->getImportId());
    }
}
