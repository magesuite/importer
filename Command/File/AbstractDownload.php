<?php

namespace MageSuite\Importer\Command\File;

abstract class AbstractDownload implements \MageSuite\Importer\Command\Command
{
    /**
     * @var \Creativestyle\LFTP\File\Downloader
     */
    protected $fileDownloader;

    public function __construct(\Creativestyle\LFTP\File\Downloader $fileDownloader)
    {
        $this->fileDownloader = $fileDownloader;
    }

    protected function setServerConfiguration($configuration)
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

    abstract public function execute($configuration);
}
