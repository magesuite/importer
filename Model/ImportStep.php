<?php

namespace MageSuite\Importer\Model;

class ImportStep extends \Magento\Framework\Model\AbstractModel
{
    public const STATUS_PENDING = 1;
    public const STATUS_IN_PROGRESS = 2;
    public const STATUS_DONE = 3;
    public const STATUS_ERROR = 4;
    public const STATUS_WARNING = 5;

    protected function _construct()
    {
        $this->_init(\MageSuite\Importer\Model\ResourceModel\ImportStep::class);
    }

    public function getReadableStatus()
    {
        $readableStatuses = [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_IN_PROGRESS => __('In progress'),
            self::STATUS_DONE => __('Done'),
            self::STATUS_ERROR => __('Error'),
            self::STATUS_WARNING => __('Warning')
        ];

        return $readableStatuses[$this->getStatus()];
    }
}
