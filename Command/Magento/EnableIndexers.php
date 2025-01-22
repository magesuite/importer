<?php

declare(strict_types=1);

namespace MageSuite\Importer\Command\Magento;

class EnableIndexers implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Framework\App\Config\Storage\WriterInterface $configWriter;
    protected \MageSuite\Importer\Model\Command\OutputFactory $outputFactory;

    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \MageSuite\Importer\Model\Command\OutputFactory $outputFactory,
    ) {
        $this->configWriter = $configWriter;
        $this->outputFactory = $outputFactory;
    }

    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        $this->configWriter->save(
            \MageSuite\Importer\Helper\Config::INDEXER_ENABLED_XML_PATH,
            '1'
        );

        return $this->outputFactory->create();
    }
}
