<?php

namespace MageSuite\Importer\Command\File;

class Copy implements \MageSuite\Importer\Command\Command
{
    /**
     * Copies path from source path to target path
     * @param $configuration
     * @return mixed
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

        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException('Source file does not exists');
        }

        copy($sourcePath, $targetPath);
    }
}
