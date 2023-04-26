<?php

namespace MageSuite\Importer\Model\ResourceModel\Import;

class Data extends \Magento\ImportExport\Model\ResourceModel\Import\Data
{
    /*
     * Memory optimized version of import data iterator
     */
    public function getIterator()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->create(
            \MageSuite\Importer\Model\Import\Data\Iterator::class
        );
    }

    public function deleteLastBunch()
    {
        $iterator = $this->getIterator();
        $bunchId = $iterator->getLastBunchId();
        $deleteCondition = $this->getConnection()->quoteInto('id = ?', $bunchId);
        $this->getConnection()->delete($this->getMainTable(), $deleteCondition);

        $iterator->previous();
        $iterator->recalculateRowsCount();
    }
}
