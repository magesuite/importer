<?php

namespace MageSuite\Importer\Command\Magento;

class EnableIndexers implements \MageSuite\Importer\Command\Command
{
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    )
    {
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
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