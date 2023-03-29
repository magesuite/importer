<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite */
$urlRewrite = $objectManager->create(\Magento\UrlRewrite\Model\UrlRewrite::class);
$urlRewrite->load('test-product.html', 'request_path');
$urlRewrite->delete();
