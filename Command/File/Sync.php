<?php

namespace MageSuite\Importer\Command\File;

class Sync extends AbstractDownload implements \MageSuite\Importer\Command\Command
{
    /**
     * Syncs folders
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration)
    {
        $this->setServerConfiguration($configuration);

        return $this->fileDownloader->sync($configuration['remote_directory'], $configuration['target_directory']);
    }
}
