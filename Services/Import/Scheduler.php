<?php

namespace MageSuite\Importer\Services\Import;

class Scheduler
{
    /**
     * @var \MageSuite\Importer\Model\ImportFactory
     */
    protected $importFactory;
    /**
     * @var \MageSuite\Importer\Model\ImportStepFactory
     */
    protected $importStepFactory;
    /**
     * @var \MageSuite\Importer\Api\ImportRepositoryInterface
     */
    protected $importRepository;

    /**
     * @var \MageSuite\Importer\Command\Magento\DisableIndexers
     */
    protected $disableIndexers;

    public function __construct(
        \MageSuite\Importer\Model\ImportFactory $importFactory,
        \MageSuite\Importer\Model\ImportStepFactory $importStepFactory,
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository,
        \MageSuite\Importer\Command\Magento\DisableIndexers $disableIndexers
    ) {
        $this->importFactory = $importFactory;
        $this->importStepFactory = $importStepFactory;
        $this->importRepository = $importRepository;
        $this->disableIndexers = $disableIndexers;
    }

    public function scheduleImport($importIdentifier) {
        $configuration = $this->importRepository->getConfigurationById($importIdentifier);

        /** @var \MageSuite\Importer\Model\Import $import */
        $import = $this->importFactory->create();

        $import->setHash(uniqid());
        $import->setImportIdentifier($importIdentifier);
        $import->save();


        foreach($configuration['steps'] as $identifier => $step) {
            $importStep = $this->importStepFactory->create();
            $importStep->setImportId($import->getId());
            $importStep->setIdentifier($identifier);

            $importStep->save();
        }

        if ($this->shouldDisableIndexers($configuration)) {
            $this->disableIndexers->execute([]);
        }
    }

    protected function shouldDisableIndexers($configuration)
    {
        if (isset($configuration['disable_indexers_when_scheduled']) && $configuration['disable_indexers_when_scheduled'] == true) {
            return true;
        }

        return false;
    }
}