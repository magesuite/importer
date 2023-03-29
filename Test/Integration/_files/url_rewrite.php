<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/**
 * @var $urlRewrite \Magento\UrlRewrite\Model\UrlRewrite
 */
$urlRewrite = $objectManager->create(\Magento\UrlRewrite\Model\UrlRewrite::class);

$urlRewrite->setRequestPath('test-product.html');
$urlRewrite->setEntityType('custom');
$urlRewrite->setTargetPath('catalog/product/view/id/10');
$urlRewrite->setStoreId(1);
$urlRewrite->save();
