<?php

namespace MageSuite\Importer\Block\Adminhtml\Import\Grid\Column\Renderer;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected \Magento\Framework\Registry $registry;
    protected \MageSuite\Importer\Model\ImportStatus $importStatus;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Context $context,
        \MageSuite\Importer\Model\ImportStatus $importStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->importStatus = $importStatus;
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        return $this->getStatus($row['status']);
    }

    protected function getStatus($status)
    {
        $statuses = [
            \MageSuite\Importer\Model\ImportStep::STATUS_ERROR => '<span class="grid-severity-critical">ERROR</span>',
            \MageSuite\Importer\Model\ImportStep::STATUS_DONE => '<span class="grid-severity-notice">DONE</span>',
            \MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS => '<span class="grid-severity-critical grid-in-progress">IN PROGRESS</span>',
            \MageSuite\Importer\Model\ImportStep::STATUS_PENDING => '<span class="grid-severity-critical grid-pending">PENDING</span>'
        ];

        return $statuses[$status];
    }
}
