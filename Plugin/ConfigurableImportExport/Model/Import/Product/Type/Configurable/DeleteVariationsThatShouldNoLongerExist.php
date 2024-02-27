<?php

namespace MageSuite\Importer\Plugin\ConfigurableImportExport\Model\Import\Product\Type\Configurable;

class DeleteVariationsThatShouldNoLongerExist
{
    protected \Magento\CatalogImportExport\Model\Import\Product $entityModel;
    protected \Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface $getProductIdsBySkus;
    protected \MageSuite\Importer\Model\ResourceModel\CatalogProductRelation $catalogProductRelation;
    protected ?string $behaviorToRestore = null;

    public function __construct(
        \Magento\CatalogImportExport\Model\Import\Product $entityModel,
        \Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface $getProductIdsBySkus,
        \MageSuite\Importer\Model\ResourceModel\CatalogProductRelation $catalogProductRelation
    ) {
        $this->entityModel = $entityModel;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->catalogProductRelation = $catalogProductRelation;
    }

    public function beforeSaveData(\Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable $subject)
    {
        $entityModel = $this->getPrivateProperty($subject, '_entityModel');
        $behavior = $entityModel->getBehavior();

        if ($behavior != \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE) {
            return;
        }

        while ($bunch = $this->entityModel->getNextBunch()) {
            $this->deleteVariationsFromBunch($bunch);
        }

        // We need temporarily to change behavior to APPEND because Magento does not handle properly loading of existing
        // super attributes when behavior is set to ADD_UPDATE
        // @see \Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable::saveData 834
        $this->behaviorToRestore = $behavior;
        $entityModel->setParameters(array_merge(
            $entityModel->getParameters(),
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND]
        ));
    }

    public function afterSaveData(\Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable $subject, $result)
    {
        if ($this->behaviorToRestore === null) {
            return $result;
        }

        $entityModel = $this->getPrivateProperty($subject, '_entityModel');
        $entityModel->setParameters(array_merge(
            $entityModel->getParameters(),
            ['behavior' => $this->behaviorToRestore]
        ));

        return $result;
    }

    protected function deleteVariationsFromBunch(array $bunch)
    {
        $parentSkus = [];
        $newVariations = [];
        $variationsToDelete = [];
        $skusToFetchIds = [];

        foreach ($bunch as $rowData) {
            if (!array_key_exists('configurable_variations', $rowData)) {
                continue;
            }

            $parentSkus[] = $rowData['sku'];
            $parsedVariations = $this->_parseVariations($rowData);

            foreach ($parsedVariations as $variation) {
                if (!isset($variation['_super_products_sku'])) {
                    continue;
                }

                $newVariations[$rowData['sku']][] = $variation['_super_products_sku'];
            }
        }

        if (empty($parentSkus)) {
            return;
        }

        $existingParentRelations = $this->catalogProductRelation->getExistingRelations($parentSkus);

        foreach ($newVariations as $parentSku => $variations) {
            if (!isset($existingParentRelations[$parentSku])) {
                continue;
            }

            $diff = array_diff($existingParentRelations[$parentSku], $variations);

            if (empty($diff)) {
                continue;
            }

            $variationsToDelete[$parentSku] = $diff;

            $skusToFetchIds[] = $parentSku;
            $skusToFetchIds = array_merge($skusToFetchIds, $variationsToDelete[$parentSku]); // phpcs:ignore
        }

        if (empty($variationsToDelete)) {
            return;
        }

        $skuToIdMapping = $this->getProductIdsBySkus->execute($skusToFetchIds);
        $parentToChildToDelete = [];

        foreach ($variationsToDelete as $parentSku => $childSkus) {
            $parentProductId = $skuToIdMapping[$parentSku];
            $childIds = [];

            foreach ($childSkus as $childSku) {
                if (!isset($skuToIdMapping[$childSku])) {
                    continue;
                }

                $childIds[] = $skuToIdMapping[$childSku];
            }

            $parentToChildToDelete[$parentProductId] = $childIds;
        }

        if (empty($parentToChildToDelete)) {
            return;
        }

        $this->catalogProductRelation->deleteRelations($parentToChildToDelete);
    }

    /**
     * Method had to be copied fully, it was not possible to use ReflectionApi to execute private method
     * @see \Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable::_parseVariations
     * phpcs:disable Standard.TooMany.IfNestedLevel.Found
     */
    protected function _parseVariations($rowData)
    {
        $additionalRows = [];
        if (empty($rowData['configurable_variations'])) {
            return $additionalRows;
        } elseif (!empty($rowData['store_view_code'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Product with assigned super attributes should not have specified "%1" value',
                    'store_view_code'
                )
            );
        }

        $variations = explode(\Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR, $rowData['configurable_variations']);
        foreach ($variations as $variation) {
            $fieldAndValuePairsText = explode($this->entityModel->getMultipleValueSeparator(), $variation);
            $additionalRow = [];

            $fieldAndValuePairs = [];
            foreach ($fieldAndValuePairsText as $nameAndValue) {
                $nameAndValue = explode(\Magento\CatalogImportExport\Model\Import\Product::PAIR_NAME_VALUE_SEPARATOR, $nameAndValue, 2);
                if ($nameAndValue) {
                    $value = isset($nameAndValue[1]) ? trim($nameAndValue[1]) : '';
                    // Ignoring field names' case.
                    $fieldName = isset($nameAndValue[0]) ? strtolower(trim($nameAndValue[0])) : '';
                    if ($fieldName) {
                        $fieldAndValuePairs[$fieldName] = $value;
                    }
                }
            }

            if (!empty($fieldAndValuePairs['sku'])) {
                $position = 0;
                $additionalRow['_super_products_sku'] = $fieldAndValuePairs['sku'];
                unset($fieldAndValuePairs['sku']);
                $additionalRow['display'] = $fieldAndValuePairs['display'] ?? 1;
                unset($fieldAndValuePairs['display']);
                foreach ($fieldAndValuePairs as $attrCode => $attrValue) {
                    $additionalRow['_super_attribute_code'] = $attrCode;
                    $additionalRow['_super_attribute_option'] = $attrValue;
                    $additionalRow['_super_attribute_position'] = $position;
                    $additionalRows[] = $additionalRow;
                    $additionalRow = [];
                    $position++;
                }
            }
        }

        return $additionalRows;
    }

    protected function getPrivateProperty($object, string $propertyName)
    {
        $reflection = new \ReflectionClass($object);

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
