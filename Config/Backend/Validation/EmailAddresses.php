<?php

namespace MageSuite\Importer\Config\Backend\Validation;

use Magento\Framework\Exception\LocalizedException;

class EmailAddresses extends \Magento\Framework\App\Config\Value
{

    public function beforeSave()
    {
        $value = $this->getValue();
        $addresses = array_map('trim', explode("\n", $value));

        foreach($addresses AS $address){
            if (!\Zend_Validate::is($address, 'EmailAddress')) {
                throw new LocalizedException(__('Please correct the email address: "%1".', $address));
            }
        }

        return $this;
    }
}
