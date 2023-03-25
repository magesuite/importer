<?php

namespace MageSuite\Importer\Observer\Command;

class CommandExecutesObserver extends AbstractCommandResultObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \MageSuite\Importer\Model\ImportStep $step */
        $step = $observer->getData('step');

        $step->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS);
        $step->setStartedAt(time());

        $this->importRepository->saveStep($step);

        $this->recalculateCurrentImportStatus($step->getImportId());
    }
}
