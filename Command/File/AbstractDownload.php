<?php

namespace MageSuite\Importer\Command\File;

abstract class AbstractDownload implements \MageSuite\Importer\Command\Command
{
    protected \Creativestyle\LFTP\File\Downloader $fileDownloader;
    protected \MageSuite\Importer\Model\Command\OutputFactory $outputFactory;

    public function __construct(
        \Creativestyle\LFTP\File\Downloader $fileDownloader,
        \MageSuite\Importer\Model\Command\OutputFactory $outputFactory,
    ) {
        $this->fileDownloader = $fileDownloader;
        $this->outputFactory = $outputFactory;
    }

    /**
     * @param string[] $configuration
     */
    protected function setServerConfiguration(array $configuration): void
    {
        if (isset($configuration['host'])) {
            $this->fileDownloader->setHost($configuration['host']);
        }

        if (isset($configuration['protocol'])) {
            $this->fileDownloader->setProtocol($configuration['protocol']);
        }

        if (isset($configuration['username'])) {
            $this->fileDownloader->setUsername($configuration['username']);
        }

        if (isset($configuration['password'])) {
            $this->fileDownloader->setPassword($configuration['password']);
        }
    }

    abstract public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output;
}
