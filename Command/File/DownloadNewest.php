<?php

namespace MageSuite\Importer\Command\File;

class DownloadNewest extends AbstractDownload implements \MageSuite\Importer\Command\Command
{
    /**
     * Downloads newest file
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration)
    {
        $this->setServerConfiguration($configuration);

        return $this->fileDownloader->downloadNewest($configuration['remote_directory'], $configuration['target_path']);
    }
}