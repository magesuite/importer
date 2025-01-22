<?php

namespace MageSuite\Importer\Command\Import;

class Parse implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Framework\ObjectManagerInterface $objectManager;
    protected \MageSuite\Importer\Model\Command\OutputFactory $outputFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \MageSuite\Importer\Model\Command\OutputFactory $outputFactory,
    ) {
        $this->objectManager = $objectManager;
        $this->outputFactory = $outputFactory;
    }

    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        /** @var \MageSuite\Importer\Command\Parser $parser */
        $parser = $this->objectManager->create($configuration['class']);

        $result = $parser->parse($configuration);

        if ($result instanceof \MageSuite\Importer\Model\Command\Output) {
            return $result;
        }

        return $this->outputFactory->create();
    }
}
