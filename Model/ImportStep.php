<?php

namespace MageSuite\Importer\Model;

class ImportStep extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_PENDING = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_DONE = 3;
    const STATUS_ERROR = 4;


    protected function _construct()
    {
        $this->_init('MageSuite\Importer\Model\ResourceModel\ImportStep');
    }

    public function getReadableStatus() {
        $readableStatuses = [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_IN_PROGRESS => __('In progress'),
            self::STATUS_DONE => __('Done'),
            self::STATUS_ERROR => __('Error')
        ];

        return $readableStatuses[$this->getStatus()];
    }
}