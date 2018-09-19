<?php

namespace MageSuite\Importer\Observer;

class AfterProductsBunchSaveObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $products = $observer->getData('bunch');

        if(empty($products)) {
            return;
        }

        foreach($products as $product) {
            \MageSuite\Importer\Model\ImportedProductsAggregator::addSku($product['sku']);
        }
    }
}