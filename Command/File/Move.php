<?php

namespace MageSuite\Importer\Command\File;

class Move implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Framework\Filesystem\Io\File $fileIo;

    public function __construct(\Magento\Framework\Filesystem\Io\File $fileIo)
    {
        $this->fileIo = $fileIo;
    }

    /**
     * Moves file from source path to target path
     */
    public function execute($configuration)
    {
        if (!isset($configuration['source_path'])) {
            throw new \InvalidArgumentException('Source path must be defined');
        }

        if (!isset($configuration['target_path'])) {
            throw new \InvalidArgumentException('Target path must be defined');
        }

        $sourcePath = BP . '/' . $configuration['source_path'];
        $targetPath = BP . '/' . $configuration['target_path'];

        if (!$this->fileIo->fileExists($sourcePath)) {
            throw new \InvalidArgumentException('Source file does not exists');
        }

        $this->fileIo->mv($sourcePath, $targetPath);
    }
}
