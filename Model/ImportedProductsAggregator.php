<?php
namespace MageSuite\Importer\Model;

// phpcs:disable Magento2.Functions.StaticFunction.StaticFunction
class ImportedProductsAggregator
{
    protected static $skus = [];

    public static function reset()
    {
        self::$skus = [];
    }

    public static function addSku($sku)
    {
        self::$skus[] = $sku;
    }

    public static function getSkus()
    {
        return self::$skus;
    }
}
