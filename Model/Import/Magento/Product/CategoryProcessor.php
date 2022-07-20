<?php

namespace MageSuite\Importer\Model\Import\Magento\Product;

class CategoryProcessor extends \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor
{
    public function reinitializeCategories()
    {
        $this->categories = [];
        $this->initCategories();
    }

    /**
     * Modification adds possibility to specify category ids during import
     * @return int
     */
    protected function upsertCategory($categoryPath)
    {
        if(is_numeric($categoryPath)) {
            return $categoryPath;
        }

        return parent::upsertCategory($categoryPath);
    }
}
