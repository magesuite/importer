<?php

namespace MageSuite\Importer\Plugin\CatalogImportExport\Model\Import\Product\Type\AbstractType;

class IgnoreDefaultValueForBestsellerAttributes
{
    const BESTSELLER_SCORE_ATTRIBUTE_NAMES = [
        'bestseller_score_by_amount',
        'bestseller_score_by_turnover',
        'bestseller_score_by_sale'
    ];

    /**
     * @var \MageSuite\Importer\Model\ImportRunningFlag
     */
    protected $importRunningFlag;

    public function __construct(\MageSuite\Importer\Model\ImportRunningFlag $importRunningFlag)
    {
        $this->importRunningFlag = $importRunningFlag;
    }

    public function afterPrepareAttributesWithDefaultValueForSave(
        \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType $subject,
        $result,
        $rowData,
        $withDefaultValue
    ) {
        if (!$this->importRunningFlag->isImportRunning()) {
            return $result;
        }

        foreach (self::BESTSELLER_SCORE_ATTRIBUTE_NAMES as $bestsellerAttributeName) {
            if (isset($result[$bestsellerAttributeName])) {
                unset($result[$bestsellerAttributeName]);
            }
        }

        return $result;
    }
}
