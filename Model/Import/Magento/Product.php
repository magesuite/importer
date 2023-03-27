<?php

namespace MageSuite\Importer\Model\Import\Magento;

class Product extends \Magento\CatalogImportExport\Model\Import\Product
{
    protected $validatedRows = [];
    protected ?\MageSuite\Importer\Services\Import\ImageManager $imageManager = null;
    protected ?\MageSuite\ThumbnailRemove\Service\ThumbnailRemover $thumbnailRemover = null;
    protected $productEntityLinkField;

    protected function getExistingImages($bunch)
    {
        $this->_eventManager->dispatch(
            'catalog_product_import_bunch_processing_before',
            ['adapter' => $this, 'bunch' => $bunch, 'sku_processor' => $this->skuProcessor]
        );

        return parent::getExistingImages($bunch);
    }

    /**
     * Optimized version of _saveProductAttributes method
     *
     * @param array $attributesData
     * @return $this
     */
    protected function _saveProductAttributes(array $attributesData)
    {
        $skus = [];

        foreach ($attributesData as $tableName => $skuData) {
            foreach ($skuData as $sku => $attributes) {
                $skus[] = $sku;
            }
        }

        $skus = array_unique($skus);

        $linkField = $this->getProductEntityLinkField();
        $select = $this->getConnection()->select()->from(
            ['e' => $this->getResource()->getTable('catalog_product_entity')],
            ['sku', $linkField]
        )->where(
            'sku IN(?)',
            $skus
        );

        $links = $this->getConnection()->fetchPairs($select);

        foreach ($attributesData as $tableName => $skuData) {
            $tableData = [];
            foreach ($skuData as $sku => $attributes) {
                $linkId = $links[$sku];

                foreach ($attributes as $attributeId => $storeValues) {
                    foreach ($storeValues as $storeId => $storeValue) {
                        $tableData[] = [
                            $linkField => $linkId,
                            'attribute_id' => $attributeId,
                            'store_id' => $storeId,
                            'value' => $storeValue,
                        ];
                    }
                }
            }
            $this->_connection->insertOnDuplicate($tableName, $tableData, ['value']);
        }

        return $this;
    }

    /**
     * Optimized version of _saveStockItem method
     *
     * @param array $attributesData
     * @return $this
     */
    protected function _saveStockItem()
    {
        $indexer = $this->indexerRegistry->get('catalog_product_category');
        /** @var $stockResource \Magento\CatalogInventory\Model\ResourceModel\Stock\Item */
        $stockResource = $this->_stockResItemFac->create();
        $entityTable = $stockResource->getMainTable();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $stockData = [];
            // Format bunch to stock data rows
            $productIdsToGetStockItems = [];

            foreach ($bunch as $rowNum => $rowData) {
                $productIdsToGetStockItems[] = $this->skuProcessor->getNewSku($rowData[self::COL_SKU])['entity_id'];
            }

            $stockItems = $this->getStockItems($productIdsToGetStockItems);

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                $row = [];
                $row['product_id'] = $this->skuProcessor->getNewSku($rowData[self::COL_SKU])['entity_id'];
                $row['website_id'] = $this->stockConfiguration->getDefaultScopeId();
                $row['stock_id'] = $this->stockRegistry->getStock($row['website_id'])->getStockId();

                $stockItemDo = $stockItems[$row['product_id']];
                $existStockData = $stockItemDo->getData();

                $row = array_merge(
                    $this->defaultStockData,
                    array_intersect_key($existStockData, $this->defaultStockData),
                    array_intersect_key($rowData, $this->defaultStockData),
                    $row
                );

                if ($this->stockConfiguration->isQty(
                    $this->skuProcessor->getNewSku($rowData[self::COL_SKU])['type_id']
                )
                ) {
                    $stockItemDo->setData($row);
                    $row['is_in_stock'] = $this->stockStateProvider->verifyStock($stockItemDo);
                    if ($this->stockStateProvider->verifyNotification($stockItemDo)) {
                        $row['low_stock_date'] = $this->dateTime->gmDate(
                            'Y-m-d H:i:s',
                            (new \DateTime())->getTimestamp()
                        );
                    }
                    $row['stock_status_changed_auto'] =
                        (int)!$this->stockStateProvider->verifyStock($stockItemDo);
                } else {
                    $row['qty'] = 0;
                }

                if (!isset($stockData[$rowData[self::COL_SKU]])) {
                    $stockData[$rowData[self::COL_SKU]] = $row;
                }
            }

            // Insert rows
            if (!empty($stockData)) {
                $this->_connection->insertOnDuplicate($entityTable, array_values($stockData));

                $this->_eventManager->dispatch(
                    'catalog_product_import_stock_item_save_after',
                    ['adapter' => $this, 'bunch' => $bunch, 'stock_data' => $stockData]
                );
            }
        }

        if (!$indexer->isScheduled()) {
            $indexer->invalidate();
        }

        return $this;
    }

    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->validatedRows[$rowNum])) {
            return $this->validatedRows[$rowNum];
        }

        $value = parent::validateRow($rowData, $rowNum);

        $this->validatedRows[$rowNum] = $value;

        return $value;
    }

    public function getDataSourceModel()
    {
        return $this->_dataSourceModel;
    }

    protected function getStockItems($productIdsToGetStockItems)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria */
        $criteria = $objectManager
            ->create(\Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory::class)
            ->create();

        $criteria->setProductsFilter([$productIdsToGetStockItems]);
        $collection = $objectManager
            ->get(\Magento\CatalogInventory\Api\StockItemRepositoryInterface::class)
            ->getList($criteria);
        $stockItemFactory = $objectManager
            ->get(\Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory::class);

        $stockItems = [];

        foreach ($productIdsToGetStockItems as $productId) {
            $stockItems[$productId] = null;
        }

        foreach ($collection->getItems() as $stockItem) {
            $stockItems[$stockItem->getProductId()] = $stockItem;
        }

        foreach ($stockItems as $productId => $stockItem) {
            if ($stockItem == null) {
                $stockItems[$productId] = $stockItemFactory->create();
            }
        }

        return $stockItems;
    }

    protected function uploadMediaFiles($fileName, $renameFileOff = false):string
    {
        $imageManager = $this->getImageManager();

        if (preg_match('/^\bhttps?:\/\//i', $fileName)) {
            return parent::uploadMediaFiles($fileName, $renameFileOff);
        }

        $baseFilePath = strtolower(basename($fileName));
        $filePath = \Magento\Framework\File\Uploader::getDispretionPath($baseFilePath) . '/' . $baseFilePath;

        if (strpos($this->_getUploader()->getTmpDir(), BP) !== 0) {
            $uploadedFilePath = BP . '/' . $this->_getUploader()->getTmpDir() . '/' . $fileName;
        } else {
            $uploadedFilePath = $this->_getUploader()->getTmpDir() . '/' . $fileName;
        }

        $fileSize = @filesize($uploadedFilePath);
        $imagePreviouslyUploaded = $imageManager->wasImagePreviouslyUploaded($filePath, $fileSize);

        if ($imagePreviouslyUploaded === \MageSuite\Importer\Services\Import\ImageManager::IMAGE_IDENTICAL) {
            return $filePath;
        }

        $imageManager->addImageFileSizeForUpdate($filePath, $fileSize);
        
        return parent::uploadMediaFiles($fileName, true);
    }

    protected function getImageManager():\MageSuite\Importer\Services\Import\ImageManager
    {
        if ($this->imageManager == null) {
            $this->imageManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\MageSuite\Importer\Services\Import\ImageManager::class);
        }

        return $this->imageManager;
    }

    protected function getThumbnailRemover()
    {
        if ($this->thumbnailRemover == null) {
            $this->thumbnailRemover = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\MageSuite\ThumbnailRemove\Service\ThumbnailRemover::class);
        }

        return $this->thumbnailRemover;
    }

    protected function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }
}
