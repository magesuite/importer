<?php

namespace MageSuite\Importer\Command\File;

class Sync extends AbstractDownload implements \MageSuite\Importer\Command\Command
{
    /**
     * Syncs folders
     */
    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        $this->setServerConfiguration($configuration);
        $this->fileDownloader->sync($configuration['remote_directory'], $configuration['target_directory']);
        return $this->outputFactory->create();
    }
}
