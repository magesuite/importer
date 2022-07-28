<?php

namespace MageSuite\Importer\Observer\Command;

class CommandErrorMailObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\Importer\Services\Notification\EmailSender
     */
    private $emailSender;

    public function __construct(
        \MageSuite\Importer\Services\Notification\EmailSender $emailSender
    ) {
        $this->emailSender = $emailSender;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \MageSuite\Importer\Model\ImportStep $step */
        $step = $observer->getData('step');
        $error = $observer->getData('error');

        $this->emailSender->notify($error, $step);
    }
}
