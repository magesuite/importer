<?php

declare(strict_types=1);

namespace MageSuite\Importer\Command\File;

abstract class FileOperation implements \MageSuite\Importer\Command\Command
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

    abstract function execute(array $configuration): \MageSuite\Importer\Model\Command\Output;

    protected function validateSourceAndTargetPaths(array $configuration): void
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
    }
}
