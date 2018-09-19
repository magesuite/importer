<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$import = $objectManager->create('MageSuite\Importer\Model\Import');

$import->setImportIdentifier('active_import')
    ->setHash('active_hash')
    ->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS)
    ->save();

$import = $objectManager->create('MageSuite\Importer\Model\Import');

$import->setImportIdentifier('active_import')
    ->setHash('done_hash')
    ->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_DONE)
    ->save();
