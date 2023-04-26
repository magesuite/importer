<?php

/** @var \Magento\ImportExport\Model\ResourceModel\Import\Data $importDataResource */
$importDataResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\ImportExport\Model\ResourceModel\Import\Data::class);
$importDataResource->cleanBunches();
