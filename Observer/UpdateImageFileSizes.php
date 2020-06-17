<?php

namespace MageSuite\Importer\Observer;

class UpdateImageFileSizes implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\Importer\Services\Import\ImageManager
     */
    protected $imageManager;

    public function __construct(\MageSuite\Importer\Services\Import\ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->imageManager->updateImageFileSizes();
    }
}
