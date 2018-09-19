<?php

namespace MageSuite\Importer\Services;

class DataValidator
{
    public function isValid($contents)
    {
        return (!empty($contents)) ? true : false;
    }
}