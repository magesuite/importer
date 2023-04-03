<?php

namespace MageSuite\Importer\Config\Backend\Validation;

class EmailAddresses extends \Magento\Framework\App\Config\Value
{

    public function beforeSave()
    {
        $value = $this->getValue();
        $addresses = array_map('trim', explode("\n", $value));

        foreach ($addresses as $address) {
            if (!\Zend_Validate::is($address, 'EmailAddress')) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please correct the email address: "%1".', $address)
                );
            }
        }

        return $this;
    }
}
