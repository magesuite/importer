<?php

declare(strict_types=1);

namespace MageSuite\Importer\Model;

class Importer extends \FireGento\FastSimpleImport\Model\Importer
{
    protected \MageSuite\Importer\Model\Command\OutputFactory $outputFactory;

    public function __construct(
        \Magento\ImportExport\Model\ImportFactory $importModelFactory,
        \FireGento\FastSimpleImport\Helper\ImportError $errorHelper,
        \FireGento\FastSimpleImport\Model\Adapters\ImportAdapterFactoryInterface $importAdapterFactory,
        \FireGento\FastSimpleImport\Helper\Config $configHelper,
        \MageSuite\Importer\Model\Command\OutputFactory $outputFactory,
    ) {
        parent::__construct($importModelFactory, $errorHelper, $importAdapterFactory, $configHelper);
        $this->outputFactory = $outputFactory;
    }

    public function setBunchGroupingField(string $field): static
    {
        $this->settings['bunch_grouping_field'] = $field;
        return $this;
    }
}
