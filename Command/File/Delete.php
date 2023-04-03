<?php

namespace MageSuite\Importer\Command\File;

class Delete implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Framework\Filesystem\Io\File $fileIo;

    public function __construct(\Magento\Framework\Filesystem\Io\File $fileIo)
    {
        $this->fileIo = $fileIo;
    }

    /**
     * Deletes file from path
     */
    public function execute($configuration)
    {
        if (!isset($configuration['path'])) {
            throw new \InvalidArgumentException('Source path must be defined');
        }

        $path = BP . '/' . $configuration['path'];

        if (!$this->fileIo->fileExists($path)) {
            return;
        }

        $this->fileIo->rm($path);
    }
}
