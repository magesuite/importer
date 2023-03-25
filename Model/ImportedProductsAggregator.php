<?php
namespace MageSuite\Importer\Model;

class ImportedProductsAggregator
{
    protected static $skus = [];

    public static function addSku($sku)
    {
        self::$skus[] = $sku;
    }

    public static function getSkus()
    {
        return self::$skus;
    }
}
