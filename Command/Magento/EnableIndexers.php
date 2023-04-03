<?php

namespace MageSuite\Importer\Command\Magento;

class EnableIndexers implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Framework\App\Config\Storage\WriterInterface $configWriter;

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
        $this->configWriter->save(
            \MageSuite\Importer\Plugin\Indexer\Model\Processor\DisableIndexer::INDEXER_ENABLED_XML_PATH,
            '1'
        );
    }
}
