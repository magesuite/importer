<?php

/** @var \Magento\Catalog\Setup\CategorySetup $installer */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Setup\CategorySetup::class
);

/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
);
$attribute->setData(
    [
        'attribute_code' => 'test_dropdown_attribute',
        'group' => 'General',
        'type' => 'int',
        'label' => 'test_dropdown_attribute',
        'backend_type' => 'int',
        'frontend_input' => 'select',
        'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
        'user_defined' => true,
        'used_in_product_listing' => true,
        'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
        'is_global' => 1,
        'is_user_defined' => 1,
        'is_used_for_promo_rules' => 1,
    ]
);
$attribute->save();
$installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());

/** @var \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement */
$attributeOptionManagement = \Magento\TestFramework\ObjectManager::getInstance()->get(\Magento\Eav\Api\AttributeOptionManagementInterface::class);

$options = ['option_1', 'option_2'];
foreach ($options as $optionName) {
    /** @var \Magento\Eav\Model\Entity\Attribute\OptionLabel $optionLabel */
    $optionLabel = \Magento\TestFramework\ObjectManager::getInstance()->create(\Magento\Eav\Model\Entity\Attribute\OptionLabel::class);
    $optionLabel->setStoreId(0);
    $optionLabel->setLabel($optionName);

    $option = \Magento\TestFramework\ObjectManager::getInstance()->create(\Magento\Eav\Model\Entity\Attribute\Option::class);
    $option->setLabel($optionLabel->getLabel());
    $option->setStoreLabels([$optionLabel]);
    $option->setSortOrder(0);
    $option->setIsDefault(false);

    $attributeOptionManagement->add(\Magento\Catalog\Model\Product::ENTITY, $attribute->getAttributeId(), $option);
}


/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
$eavConfig->clear();
