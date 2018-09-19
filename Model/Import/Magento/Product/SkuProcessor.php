<?php

namespace MageSuite\Importer\Model\Import\Magento\Product;

class SkuProcessor extends \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor
{
    /**
     * Performance optimisation for getOldSkus method.
     *
     * @return array
     */
    public function getOldSkus()
    {
        if (!is_array($this->oldSkus)) {
            $this->oldSkus = $this->_getSkus();
        }

        return $this->oldSkus;
    }
}