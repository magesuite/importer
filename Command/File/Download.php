<?php

namespace MageSuite\Importer\Command\File;

class Download extends AbstractDownload implements \MageSuite\Importer\Command\Command
{
    /**
     * Downloads file
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration)
    {
        $this->setServerConfiguration($configuration);

        return $this->fileDownloader->download($configuration['remote_path'], $configuration['target_path']);
    }
}