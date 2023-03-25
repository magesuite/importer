<?php

namespace MageSuite\Importer\Model\Import\Magento;

class AdvancedPricing extends \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing
{
    /**
     * Fixing bug that deletes always first batch of products
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceAdvancedPrices()
    {
        $behavior = $this->getBehavior();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $tierPrices = [];
            $listSku = [];

            if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                $this->_cachedSkuToDelete = null;
            }

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(\Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowSku = $rowData[self::COL_SKU];
                $listSku[] = $rowSku;
                if (!empty($rowData[self::COL_TIER_PRICE_WEBSITE])) {
                    $tierPrices[$rowSku][] = [
                        'all_groups' => $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS,
                        'customer_group_id' => $this->getCustomerGroupId(
                            $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP]
                        ),
                        'qty' => $rowData[self::COL_TIER_PRICE_QTY],
                        'value' => $rowData[self::COL_TIER_PRICE],
                        'website_id' => $this->getWebsiteId($rowData[self::COL_TIER_PRICE_WEBSITE])
                    ];
                }
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                if ($listSku) {
                    $this->processCountNewPrices($tierPrices);
                    if ($this->deleteProductTierPrices(array_unique($listSku), self::TABLE_TIER_PRICE)) {
                        $this->saveProductPrices($tierPrices, self::TABLE_TIER_PRICE);
                        $this->setUpdatedAt($listSku);
                    }
                }
            } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
                $this->processCountExistingPrices($tierPrices, self::TABLE_TIER_PRICE)
                    ->processCountNewPrices($tierPrices);
                $this->saveProductPrices($tierPrices, self::TABLE_TIER_PRICE);
                if ($listSku) {
                    $this->setUpdatedAt($listSku);
                }
            }
        }
        return $this;
    }
}
