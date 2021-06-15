<?php

namespace MageSuite\Importer\Plugin\ImportExport\Model\Import\Entity\AbstractEntity;

class SetRunningImportFlag
{
    /**
     * @var \MageSuite\Importer\Model\ImportRunningFlag
     */
    protected $importRunningFlag;

    public function __construct(\MageSuite\Importer\Model\ImportRunningFlag $importRunningFlag)
    {
        $this->importRunningFlag = $importRunningFlag;
    }

    public function beforeImportData(
        \Magento\ImportExport\Model\Import\Entity\AbstractEntity $subject
    ) {
        $this->importRunningFlag->setIsRunning(true);
    }

    public function afterImportData(
        \Magento\ImportExport\Model\Import\Entity\AbstractEntity $subject,
        $result
    ) {
        $this->importRunningFlag->setIsRunning(false);
    }
}
