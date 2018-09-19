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
        $error = $observer->getData('error');

        $step->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_ERROR);
        $step->setFinishedAt(time());
        $step->setError($error);

        $this->importRepository->saveStep($step);

        $this->recalculateCurrentImportStatus($step->getImportId());
    }
}