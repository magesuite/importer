<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $mediaConfig \Magento\Catalog\Model\Product\Media\Config */
$mediaConfig = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');

/** @var $mediaDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
$targetDirPath = $mediaConfig->getBaseMediaPath() . str_replace('/', DIRECTORY_SEPARATOR, '/m/a/');

$targetTmpDirPath = $mediaConfig->getBaseTmpMediaPath() . str_replace('/', DIRECTORY_SEPARATOR, '/m/a/');

$mediaDirectory->create($targetDirPath);
$mediaDirectory->create($targetTmpDirPath);

$targetTmpFilePath = $mediaDirectory->getAbsolutePath() . $targetTmpDirPath . '%s';

copy(__DIR__ . '/'.$image, sprintf($targetTmpFilePath, $image));
// Copying the image to target dir is not necessary because during product save, it will be moved there from tmp dir
