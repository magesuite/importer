<?php

namespace MageSuite\Importer\Command\File;

class CreateDirectories implements \MageSuite\Importer\Command\Command
{
    protected \Magento\Framework\Filesystem\Io\File $fileIo;
    protected \MageSuite\Importer\Model\Command\OutputFactory $outputFactory;

    public function __construct(
        \Magento\Framework\Filesystem\Io\File $fileIo,
        \MageSuite\Importer\Model\Command\OutputFactory $outputFactory,
    ) {
        $this->fileIo = $fileIo;
        $this->outputFactory = $outputFactory;
    }

    /**
     * Creates directories specified in configuration
     */
    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        $directoriesPaths = $configuration['directories_paths'] ?? null;

        if ($directoriesPaths == null) {
            return $this->outputFactory->create();
        }

        foreach ($directoriesPaths as $directoryPath) {
            $directoryPath = BP . DIRECTORY_SEPARATOR . $directoryPath;

            if ($this->fileIo->fileExists($directoryPath)) {
                continue;
            }

            $this->fileIo->mkdir($directoryPath, 0777, true);
        }

        return $this->outputFactory->create();
    }
}
