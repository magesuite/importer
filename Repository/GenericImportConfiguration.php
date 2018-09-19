<?php

namespace MageSuite\Importer\Repository;

class GenericImportConfiguration implements ImportConfiguration
{
    public function getById($id)
    {
        $jsonConfiguration = file_get_contents(BP . DIRECTORY_SEPARATOR . 'import.json');

        return json_decode($jsonConfiguration, true);
    }
}