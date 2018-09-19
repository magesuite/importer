<?php

namespace MageSuite\Importer\Helper;

class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getFailedImportThreshold() {
        return $this->scopeConfig->getValue('importer/configuration/failed_import_threshold');
    }
}