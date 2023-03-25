<?php

namespace MageSuite\Importer\Command\File;

class DownloadFromUrl extends AbstractDownload implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Framework\Filesystem\Io\File $fileIo;

    public function __construct()
    {
        $this->fileIo = new \Magento\Framework\Filesystem\Io\File();
    }

    /**
     * Downloads file from remote URL
     */
    public function execute($configuration)
    {
        $this->setServerConfiguration($configuration);
        $contents = $this->fileIo->read($configuration['remote_url']);
        $this->fileIo->write($configuration['target_path'], $contents);

        return true;
    }
}
