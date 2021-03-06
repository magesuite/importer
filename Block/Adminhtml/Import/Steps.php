<?php

namespace MageSuite\Importer\Block\Adminhtml\Import;

class Steps extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data
    )
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    public function getSteps() {
        return $this->registry->registry('import_steps');
    }

    public function getSeverityClass($status) {
        $statuses = [
            \MageSuite\Importer\Model\ImportStep::STATUS_ERROR => 'grid-severity-critical',
            \MageSuite\Importer\Model\ImportStep::STATUS_DONE => 'grid-severity-notice',
            \MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS => 'grid-severity-critical grid-in-progress',
            \MageSuite\Importer\Model\ImportStep::STATUS_PENDING => 'grid-severity-critical grid-pending'
        ];

        return $statuses[$status];
    }
}