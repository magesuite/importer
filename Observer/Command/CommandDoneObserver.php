<?php

namespace MageSuite\Importer\Observer\Command;

class CommandDoneObserver extends AbstractCommandResultObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \MageSuite\Importer\Model\ImportStep $step */
        $step = $observer->getData('step');
        $output = $observer->getData('output');

        $step->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_DONE);
        $step->setOutput($output);
        $step->setFinishedAt(time());

        $this->importRepository->saveStep($step);

        $this->recalculateCurrentImportStatus($step->getImportId());
    }
}