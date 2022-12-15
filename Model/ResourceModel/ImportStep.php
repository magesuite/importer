<?php

namespace MageSuite\Importer\Model\ResourceModel;

class ImportStep extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('import_log_step', 'id');
    }

    public function getFirstRowOlderThan($days)
    {
        $olderThanDate= date('Y-m-d', strtotime(date('Y-m-d') . '-' . $days . ' days'));

        $select = $this->getConnection()->select();
        $select->from($this->getConnection()->getTableName('import_log_step'))
            ->where('started_at < ?', [$olderThanDate])
            ->order('import_id DESC')
            ->limit(1);

        return $this->getConnection()->fetchRow($select);
    }
}
