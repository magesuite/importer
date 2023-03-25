<?php

namespace MageSuite\Importer\Command\File;

class DownloadFromUrl extends AbstractDownload implements \MageSuite\Importer\Command\Command
{
    /**
     * Downloads file from remote URL
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration)
    {
        $this->setServerConfiguration($configuration);

        $contents = file_get_contents($configuration['remote_url']);

        file_put_contents($configuration['target_path'], $contents);

        return true;
    }
}
