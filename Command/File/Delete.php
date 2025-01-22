<?php

namespace MageSuite\Importer\Command\File;

class Delete implements \MageSuite\Importer\Command\Command
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
     * Deletes file from path
     */
    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        if (!isset($configuration['path'])) {
            throw new \InvalidArgumentException('Source path must be defined');
        }

        $path = BP . '/' . $configuration['path'];

        if (!$this->fileIo->fileExists($path)) {
            return $this->outputFactory->create()
                ->setMessage('File does not exist')
                ->setStatus(\MageSuite\Importer\Model\ImportStep::STATUS_WARNING);
        }

        $this->fileIo->rm($path);

        return $this->outputFactory->create();
    }
}
