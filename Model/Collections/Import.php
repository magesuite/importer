<?php

namespace MageSuite\Importer\Model\Collections;

class Import extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \MageSuite\Importer\Model\Import::class,
            \MageSuite\Importer\Model\ResourceModel\Import::class
        );
    }
}
