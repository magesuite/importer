<?php

declare(strict_types=1);

namespace MageSuite\Importer\Command\File;

class Download extends AbstractDownload implements \MageSuite\Importer\Command\Command
{
    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        $this->setServerConfiguration($configuration);
        $this->fileDownloader->download($configuration['remote_path'], $configuration['target_path']);
        return $this->outputFactory->create();
    }
}
