<?php
namespace MageSuite\Importer\Model;

class ImportedProductsAggregator
{
    private static $skus = [];

    public static function addSku($sku)
    {
        self::$skus[] = $sku;
    }

    public static function getSkus()
    {
        return self::$skus;
    }
}
