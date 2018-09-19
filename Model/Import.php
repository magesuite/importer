<?php

namespace MageSuite\Importer\Model;

class Import extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('MageSuite\Importer\Model\ResourceModel\Import');
    }
}