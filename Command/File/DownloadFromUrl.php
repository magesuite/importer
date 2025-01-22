<?php

namespace MageSuite\Importer\Command\File;

class DownloadFromUrl extends AbstractDownload implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Framework\Filesystem\Io\File $fileIo;

    public function __construct(\Magento\Framework\Filesystem\Io\File $fileIo)
    {
        $this->fileIo = $fileIo;
    }

    /**
     * Downloads file from remote URL
     */
    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        $this->setServerConfiguration($configuration);
        $contents = $this->fileIo->read($configuration['remote_url']);
        $this->fileIo->write($configuration['target_path'], $contents);

        return $this->outputFactory->create();
    }
}
