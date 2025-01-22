<?php

declare(strict_types=1);

namespace MageSuite\Importer\Command\File;

class Move extends FileOperation
{
    public function execute(array $configuration): \MageSuite\Importer\Model\Command\Output
    {
        $this->validateSourceAndTargetPaths($configuration);
        $this->fileIo->mv(
            BP . '/' . $configuration['source_path'],
            BP . '/' . $configuration['target_path']
        );
        return $this->outputFactory->create();
    }
}
