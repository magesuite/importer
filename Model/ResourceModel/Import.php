<?php


namespace MageSuite\Importer\Model\ResourceModel;


class Import extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('import_log', 'import_id');
    }
}