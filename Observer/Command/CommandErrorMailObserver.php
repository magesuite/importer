<?php

namespace MageSuite\Importer\Observer\Command;

class CommandErrorMailObserver implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\Importer\Services\Notification\EmailSender $emailSender;

    public function __construct(
        \MageSuite\Importer\Services\Notification\EmailSender $emailSender
    ) {
        $this->emailSender = $emailSender;
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        /** @var \MageSuite\Importer\Model\ImportStep $step */
        $step = $observer->getData('step');
        $error = $observer->getData('error');

        $this->emailSender->notify($error, $step);
    }
}
