<?php

namespace MageSuite\Importer\Model\Import\Product;

/**
 * Class was overwritten because protected and private methods had to be modified to include custom error messages
 * @package MageSuite\Importer\Model\Import\Product
 */
class Validator extends \Magento\CatalogImportExport\Model\Import\Product\Validator
{
    const INVALID_OPTION_VALUE_ERROR_MESSAGE = "Value for '%s' attribute contains incorrect value '%s' for product with SKU '%s', see acceptable values on settings specified for Admin";
    const INVALID_VALUE_LENGTH_ERROR_MESSAGE = "Attribute '%s' exceeded max length for product with SKU '%s'";
    const INVALID_NUMERIC_VALUE_ERROR_MESSAGE = "Value '%s' for '%s' attribute contains non numeric value for product with SKU: '%s'";
    const VALUE_IS_REQUIRED_MESSAGE_WITH_SKU = "Please make sure attribute '%s' is not empty for product with SKU: '%s'.";

    /**
     * @param string $attrCode
     * @param array $attrParams
     * @param array $rowData
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isAttributeValid($attrCode, array $attrParams, array $rowData)
    {
        $this->_rowData = $rowData;

        if (isset($rowData['product_type']) && !empty($attrParams['apply_to'])
            && !in_array($rowData['product_type'], $attrParams['apply_to'])
        ) {
            return true;
        }

        if (!$this->isRequiredAttributeValid($attrCode, $attrParams, $rowData)) {
            $valid = false;
            if(isset($rowData['sku']) and !empty($rowData['sku'])) {
                $this->_addMessages(
                    [
                        sprintf(
                            self::VALUE_IS_REQUIRED_MESSAGE_WITH_SKU,
                            $attrCode,
                            $rowData['sku']
                        )
                    ]
                );
            } else {
                $this->_addMessages(
                    [
                        sprintf(
                            $this->context->retrieveMessageTemplate(
                                \Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::ERROR_VALUE_IS_REQUIRED
                            ),
                            $attrCode
                        )
                    ]
                );
            }

            return $valid;
        }

        if (!strlen(trim($rowData[$attrCode]))) {
            return true;
        }
        switch ($attrParams['type']) {
            case 'varchar':
            case 'text':
                $valid = $this->textValidation($attrCode, $attrParams['type']);
                break;
            case 'decimal':
            case 'int':
                $valid = $this->numericValidation($attrCode, $attrParams['type']);
                break;
            case 'select':
            case 'boolean':
                $valid = $this->validateOption($attrCode, $attrParams['options'], $rowData[$attrCode]);
                break;
            case 'multiselect':
                $values = $this->context->parseMultiselectValues($rowData[$attrCode]);
                foreach ($values as $value) {
                    $valid = $this->validateOption($attrCode, $attrParams['options'], $value);
                    if (!$valid) {
                        break;
                    }
                }
                break;
            case 'datetime':
                $val = trim($rowData[$attrCode]);
                $valid = strtotime($val) !== false;
                if (!$valid) {
                    $this->_addMessages([\Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE]);
                }
                break;
            default:
                $valid = true;
                break;
        }

        if ($valid && !empty($attrParams['is_unique'])) {
            if (isset($this->_uniqueAttributes[$attrCode][$rowData[$attrCode]])
                && ($this->_uniqueAttributes[$attrCode][$rowData[$attrCode]] != $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU])) {
                $this->_addMessages([\Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::ERROR_DUPLICATE_UNIQUE_ATTRIBUTE]);
                return false;
            }
            $this->_uniqueAttributes[$attrCode][$rowData[$attrCode]] = $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU];
        }

        if (!$valid) {
            $this->setInvalidAttribute($attrCode);
        }

        return (bool)$valid;
    }

    /**
     * Check if value is valid attribute option
     *
     * @param string $attrCode
     * @param array $possibleOptions
     * @param string $value
     * @return bool
     */
    private function validateOption($attrCode, $possibleOptions, $value)
    {
        if (!isset($possibleOptions[strtolower($value)])) {
            $this->_addMessages(
                [
                    sprintf(
                        self::INVALID_OPTION_VALUE_ERROR_MESSAGE,
                        $attrCode,
                        $value,
                        isset($this->_rowData['sku']) ? $this->_rowData['sku'] : ''
                    )
                ]
            );
            return false;
        }
        return true;
    }

    /**
     * @param mixed $attrCode
     * @param string $type
     * @return bool
     */
    protected function textValidation($attrCode, $type)
    {
        $val = $this->string->cleanString($this->_rowData[$attrCode]);
        if ($type == 'text') {
            $valid = $this->string->strlen($val) < \Magento\CatalogImportExport\Model\Import\Product::DB_MAX_TEXT_LENGTH;
        } else {
            $valid = $this->string->strlen($val) < \Magento\CatalogImportExport\Model\Import\Product::DB_MAX_VARCHAR_LENGTH;
        }
        if (!$valid) {
            $this->_addMessages([sprintf(
                self::INVALID_VALUE_LENGTH_ERROR_MESSAGE,
                $attrCode,
                isset($this->_rowData['sku']) ? $this->_rowData['sku'] : ''
            )]);
        }
        return $valid;
    }

    /**
     * @param mixed $attrCode
     * @param string $type
     * @return bool
     */
    protected function numericValidation($attrCode, $type)
    {
        $val = trim($this->_rowData[$attrCode]);
        if ($type == 'int') {
            $valid = (string)(int)$val === $val;
        } else {
            $valid = is_numeric($val);
        }
        if (!$valid) {
            $this->_addMessages(
                [
                    sprintf(
                        self::INVALID_NUMERIC_VALUE_ERROR_MESSAGE,
                        $this->_rowData[$attrCode],
                        $attrCode,
                        isset($this->_rowData['sku']) ? $this->_rowData['sku'] : ''
                    )
                ]
            );
        }
        return $valid;
    }

}