<?php

namespace MageSuite\Importer\Command\File;

class CreateDirectories implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Framework\Filesystem\Io\File $fileIo;

    public function __construct(\Magento\Framework\Filesystem\Io\File $fileIo)
    {
        $this->fileIo = $fileIo;
    }

    /**
     * Creates directories specified in configuration
     */
    public function execute($configuration)
    {
        $directoriesPaths = isset($configuration['directories_paths']) ? $configuration['directories_paths'] : null;

        if ($directoriesPaths == null) {
            return;
        }

        foreach ($directoriesPaths as $directoryPath) {
            $directoryPath = BP . DIRECTORY_SEPARATOR . $directoryPath;

            if ($this->fileIo->fileExists($directoryPath)) {
                continue;
            }

            $this->fileIo->mkdir($directoryPath, 0777, true);
        }
    }
}
