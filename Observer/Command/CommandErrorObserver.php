<?php

namespace MageSuite\Importer\Observer\Command;

class CommandErrorObserver extends AbstractCommandResultObserver  implements \Magento\Framework\Event\ObserverInterface
{
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
        $newError = sprintf("%s\n Error at attempt #%s: \n %s", $existingError, $attempt, $error);

        $step->setError($newError);

        if($wasFinalAttempt) {
            $step->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_ERROR);
            $step->setFinishedAt(time());
        }

        $this->importRepository->saveStep($step);

        $this->recalculateCurrentImportStatus($step->getImportId());
    }
}