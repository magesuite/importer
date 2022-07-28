<?php

namespace MageSuite\Importer\Observer;

class SendNotificationAboutSilentErrors implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\Importer\Services\Notification\EmailSender
     */
    protected $emailSender;

    public function __construct(\MageSuite\Importer\Services\Notification\EmailSender $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\CatalogImportExport\Model\Import\Product $adapter */
        $adapter = $observer->getAdapter();

        $errors = $adapter->getErrorAggregator()->getAllErrors();
        if (empty($errors)) {
            return;
        }

        $this->emailSender->notify($errors);
    }
}
