<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$import = $objectManager->create('MageSuite\Importer\Model\Import');

$import->setImportIdentifier('finished_import')
    ->setHash('finished_import_1')
    ->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_DONE)
    ->save();

$import = $objectManager->create('MageSuite\Importer\Model\Import');

$import->setImportIdentifier('finished_import')
    ->setHash('finished_import_2')
    ->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_ERROR)
    ->save();
