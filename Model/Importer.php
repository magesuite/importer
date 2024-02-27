<?php

namespace MageSuite\Importer\Model;

class Importer extends \FireGento\FastSimpleImport\Model\Importer
{
    public function setBunchGroupingField(string $field)
    {
        $this->settings['bunch_grouping_field'] = $field;
        return $this;
    }
}
