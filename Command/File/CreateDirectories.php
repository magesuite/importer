<?php

namespace MageSuite\Importer\Command\File;

class CreateDirectories implements \MageSuite\Importer\Command\Command
{

    /**
     * Creates directories specified in configuration
     * @param $configuration
     * @return mixed
     */
    public function execute($configuration)
    {
        $directoriesPaths = isset($configuration['directories_paths']) ? $configuration['directories_paths'] : null;

        if ($directoriesPaths == null) {
            return;
        }

        foreach ($directoriesPaths as $directoryPath) {
            $directoryPath = BP . DIRECTORY_SEPARATOR . $directoryPath;

            if (file_exists($directoryPath)) {
                continue;
            }

            mkdir($directoryPath, 0777, true);
        }
    }
}
