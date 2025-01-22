<?php

declare(strict_types=1);

namespace MageSuite\Importer\Command\File;

class DownloadNewest extends AbstractDownload implements \MageSuite\Importer\Command\Command
{
    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        $this->setServerConfiguration($configuration);
        $this->fileDownloader->downloadNewest($configuration['remote_directory'], $configuration['target_path']);
        return $this->outputFactory->create();
    }
}
