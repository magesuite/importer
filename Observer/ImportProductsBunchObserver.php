<?php

namespace MageSuite\Importer\Observer;

class ImportProductsBunchObserver implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\Importer\Services\Import\ProductRelationsManager $productRelationsManager;

    public function __construct(
        \MageSuite\Importer\Services\Import\ProductRelationsManager $productRelationsManager
    ) {
        $this->productRelationsManager = $productRelationsManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $products = $observer->getData('bunch');
        $skuProcessor = $observer->getData('sku_processor');

        $this->productRelationsManager->deleteRelations($products, $skuProcessor);
    }
}
