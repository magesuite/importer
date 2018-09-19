<?php

namespace MageSuite\Importer\Model\ResourceModel\Import;

class Data extends \Magento\ImportExport\Model\ResourceModel\Import\Data
{

    /*
     * Memory optimized version of import data iterator
     */
    public function getIterator()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(
            \MageSuite\Importer\Model\Import\Data\Iterator::class
        );
    }
}