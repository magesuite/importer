<?php

namespace MageSuite\Importer\Command\Import;

class Parse implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Parses file to output path
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration)
    {
        /** @var \MageSuite\Importer\Command\Parser $parser */
        $parser = $this->objectManager->create($configuration['class']);

        return $parser->parse($configuration);
    }
}
