<?php

namespace MageSuite\Importer\Command\Magento;

class EnableIndexers implements \MageSuite\Importer\Command\Command
{
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;
    public function __construct(\Magento\Framework\App\Config\Storage\WriterInterface $configWriter)
    {
        $this->configWriter = $configWriter;
    }

    /**
     * Command is disabling indexers
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration)
    {
        $this->configWriter->save(\MageSuite\Importer\Plugin\DisableIndexer::INDEXER_ENABLED_XML_PATH, '1');
    }
}
