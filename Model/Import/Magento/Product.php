<?php

namespace MageSuite\Importer\Model\Import\Magento;

class Product extends \Magento\CatalogImportExport\Model\Import\Product
{
    protected $validatedRows = [];

    /**
     * @var \MageSuite\Importer\Services\Import\ImageManager
     */
    protected $imageManager = null;

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

        $select = $this->getConnection()->select()->from(
            ['e' => $this->getResource()->getTable('catalog_product_entity')],
            ['sku', 'entity_id']
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
                            'entity_id' => $linkId,
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

            $websiteId = $this->stockConfiguration->getDefaultScopeId();

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


    private function getStockItems($productIdsToGetStockItems)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria */
        $criteria = $objectManager->create('Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory')->create();

        $criteria->setProductsFilter([$productIdsToGetStockItems]);
        $collection = $objectManager->get('Magento\CatalogInventory\Api\StockItemRepositoryInterface')->getList($criteria);
        $stockItemFactory = $objectManager->get('\Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory');

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

    /**
     * Optimized version of _saveLinks method
     *
     * @param array $attributesData
     * @return $this
     */
    protected function _saveLinks()
    {
        $resource = $this->_linkFactory->create();
        $mainTable = $resource->getMainTable();
        $positionAttrId = [];
        $nextLinkId = $this->_resourceHelper->getNextAutoincrement($mainTable);

        // pre-load 'position' attributes ID for each link type once
        foreach ($this->_linkNameToId as $linkName => $linkId) {
            $select = $this->_connection->select()->from(
                $resource->getTable('catalog_product_link_attribute'),
                ['id' => 'product_link_attribute_id']
            )->where(
                'link_type_id = :link_id AND product_link_attribute_code = :position'
            );
            $bind = [':link_id' => $linkId, ':position' => 'position'];
            $positionAttrId[$linkId] = $this->_connection->fetchOne($select, $bind);
        }
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $productIds = [];
            $linkRows = [];
            $positionRows = [];

            $productLinkKeys = [];
            $productIdsToLoadLinks = [];

            foreach ($bunch as $rowNum => $rowData) {
                $sku = $rowData[self::COL_SKU];

                $productId = $this->skuProcessor->getNewSku($sku)['entity_id'];

                $productIdsToLoadLinks[] = $productId;
            }

            $select = $this->_connection->select()->from(
                $resource->getTable('catalog_product_link'),
                ['id' => 'link_id', 'linked_id' => 'linked_product_id', 'link_type_id' => 'link_type_id']
            )->where(
                'product_id IN (?)', $productIdsToLoadLinks
            );

            foreach ($this->_connection->fetchAll($select, $bind) as $linkData) {
                $linkKey = "{$productId}-{$linkData['linked_id']}-{$linkData['link_type_id']}";
                $productLinkKeys[$linkKey] = $linkData['id'];
            }

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                $sku = $rowData[self::COL_SKU];

                $productId = $this->skuProcessor->getNewSku($sku)['entity_id'];

                foreach ($this->_linkNameToId as $linkName => $linkId) {
                    $productIds[] = $productId;
                    if (isset($rowData[$linkName . 'sku'])) {
                        $linkSkus = explode($this->getMultipleValueSeparator(), $rowData[$linkName . 'sku']);
                        $linkPositions = !empty($rowData[$linkName . 'position'])
                            ? explode($this->getMultipleValueSeparator(), $rowData[$linkName . 'position'])
                            : [];
                        foreach ($linkSkus as $linkedKey => $linkedSku) {
                            $linkedSku = trim($linkedSku);
                            if ((!is_null(
                                        $this->skuProcessor->getNewSku($linkedSku)
                                    ) || isset(
                                        $this->_oldSku[$linkedSku]
                                    )) && $linkedSku != $sku
                            ) {
                                $newSku = $this->skuProcessor->getNewSku($linkedSku);
                                if (!empty($newSku)) {
                                    $linkedId = $newSku['entity_id'];
                                } else {
                                    $linkedId = $this->_oldSku[$linkedSku]['entity_id'];
                                }

                                if ($linkedId == null) {
                                    // Import file links to a SKU which is skipped for some reason,
                                    // which leads to a "NULL"
                                    // link causing fatal errors.
                                    continue;
                                }

                                $linkKey = "{$productId}-{$linkedId}-{$linkId}";
                                if (empty($productLinkKeys[$linkKey])) {
                                    $productLinkKeys[$linkKey] = $nextLinkId;
                                }
                                if (!isset($linkRows[$linkKey])) {
                                    $linkRows[$linkKey] = [
                                        'link_id' => $productLinkKeys[$linkKey],
                                        'product_id' => $productId,
                                        'linked_product_id' => $linkedId,
                                        'link_type_id' => $linkId,
                                    ];
                                    if (!empty($linkPositions[$linkedKey])) {
                                        $positionRows[] = [
                                            'link_id' => $productLinkKeys[$linkKey],
                                            'product_link_attribute_id' => $positionAttrId[$linkId],
                                            'value' => $linkPositions[$linkedKey],
                                        ];
                                    }
                                    $nextLinkId++;
                                }
                            }
                        }
                    }
                }
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND != $this->getBehavior() && $productIds) {
                $this->_connection->delete(
                    $mainTable,
                    $this->_connection->quoteInto('product_id IN (?)', array_unique($productIds))
                );
            }
            if ($linkRows) {
                $this->_connection->insertOnDuplicate($mainTable, $linkRows, ['link_id']);
            }
            if ($positionRows) {
                // process linked product positions
                $this->_connection->insertOnDuplicate(
                    $resource->getAttributeTypeTable('int'),
                    $positionRows, ['value']
                );
            }
        }
        return $this;
    }

    protected function uploadMediaFiles($fileName, $renameFileOff = false)
    {
        /** @var \MageSuite\Importer\Services\Import\ImageManager $imageManager */
        $imageManager = $this->getImageManager();

        if (preg_match('/^\bhttps?:\/\//i', $fileName)) {
            return parent::uploadMediaFiles($fileName, $renameFileOff);
        }

        $baseFilePath = strtolower(basename($fileName));
        $filePath = \Magento\Framework\File\Uploader::getDispretionPath($baseFilePath) . '/' . $baseFilePath;

        if(strpos($this->_getUploader()->getTmpDir(), BP) !== 0){
            $uploadedFilePath = BP . '/' . $this->_getUploader()->getTmpDir() . '/' . $fileName;
        }else{
            $uploadedFilePath = $this->_getUploader()->getTmpDir() . '/' . $fileName;
        }

        $fileSize = filesize($uploadedFilePath);

        if ($imageManager->wasImagePreviouslyUploaded($filePath, $fileSize)) {
            return $filePath;
        }

        $return = parent::uploadMediaFiles($fileName, true);

        $imageManager->insertImageMetadata($filePath, $fileSize);

        return $return;
    }

    /**
     * @return mixed
     */
    protected function getImageManager()
    {
        if ($this->imageManager == null) {
            $this->imageManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\MageSuite\Importer\Services\Import\ImageManager::class);
        }

        return $this->imageManager;
    }
}