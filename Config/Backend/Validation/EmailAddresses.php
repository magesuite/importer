<?php

namespace MageSuite\Importer\Config\Backend\Validation;

class EmailAddresses extends \Magento\Framework\App\Config\Value
{
    protected \Laminas\Validator\EmailAddress $emailAddressValidator;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Laminas\Validator\EmailAddress $emailAddressValidator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->emailAddressValidator = $emailAddressValidator;
    }

    public function beforeSave()
    {
        $value = $this->getValue();
        $addresses = array_map('trim', explode("\n", $value));

        foreach ($addresses as $address) {
            if (!$this->emailAddressValidator->isValid($address)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please correct the email address: "%1".', $address)
                );
            }
        }

        return $this;
    }
}
