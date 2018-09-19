<?php

namespace MageSuite\Importer\Model\ResourceModel;

class ImportStep extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('import_log_step', 'id');
    }
}