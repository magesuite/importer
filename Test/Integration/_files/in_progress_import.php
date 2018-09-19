<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$import = $objectManager->create('MageSuite\Importer\Model\Import');

$import->setImportIdentifier('in_progress_import')
    ->setHash('in_progress')
    ->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS)
    ->save();

$importStep = $objectManager->create('MageSuite\Importer\Model\ImportStep');

$importStep->setIdentifier('import')
    ->setImportId($import->getId())
    ->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_IN_PROGRESS)
    ->setStartedAt('2018-07-19 08:14:18')
    ->save();
