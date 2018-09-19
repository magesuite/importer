<?php

namespace MageSuite\Importer\Command\File;

class Delete implements \MageSuite\Importer\Command\Command
{
    /**
     * Deletes file from path
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration)
    {
        if(!isset($configuration['path'])) {
            throw new \InvalidArgumentException('Source path must be defined');
        }

        $path = BP . '/' . $configuration['path'];

        if(!file_exists($path)) {
            return;
        }

        unlink($path);
    }
}