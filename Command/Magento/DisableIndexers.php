<?php

namespace MageSuite\Importer\Command\Magento;

class DisableIndexers implements \MageSuite\Importer\Command\Command
{
    /**
     * @var \MageSuite\Importer\Model\Command\KillIndexers
     */
    protected $killIndexers;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \MageSuite\Importer\Model\Command\KillIndexers $killIndexers
    )
    {
        $this->configWriter = $configWriter;
        $this->killIndexers = $killIndexers;
    }

    /**
     * Command is disabling indexers
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration)
    {
        $this->configWriter->save(\MageSuite\Importer\Plugin\DisableIndexer::INDEXER_ENABLED_XML_PATH, '0');

        $this->killIndexers->execute();
    }
}