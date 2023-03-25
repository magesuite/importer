<?php

namespace MageSuite\Importer\Model\Collections;

class ImportStep extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('MageSuite\Importer\Model\ImportStep', 'MageSuite\Importer\Model\ResourceModel\ImportStep');
    }
}
