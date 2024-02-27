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

    public function getIteratorForCustomQuery($ids)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->create(
            \MageSuite\Importer\Model\Import\Data\Iterator::class
        );
    }

    public function deleteLastBunch(): void
    {
        $this->_iterator->previous();
        $bunchId = $this->_iterator->getLastBunchId();
        $deleteCondition = $this->getConnection()->quoteInto('id = ?', $bunchId);
        $this->getConnection()->delete($this->getMainTable(), $deleteCondition);
    }

    /**
     * Port of the method for Magento versions lower than 2.4.6
     * We only use updated_at here because it was impossible to also port logic related to is_processed
     */
    public function cleanProcessedBunches()
    {
        if(method_exists(parent::class, 'cleanProcessedBunches')) {
            parent::cleanProcessedBunches();
        }

        $this->getConnection()->delete(
            $this->getMainTable(),
            'TIMESTAMPADD(DAY, 1, updated_at) < CURRENT_TIMESTAMP() '
        );
    }
}
