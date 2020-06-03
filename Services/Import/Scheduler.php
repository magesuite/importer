<?php

namespace MageSuite\Importer\Services\Import;

class Scheduler
{
    /**
     * @var \MageSuite\Importer\Model\Command\KillIndexers
     */
    protected $killIndexers;

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

    public function __construct(
        \MageSuite\Importer\Model\ImportFactory $importFactory,
        \MageSuite\Importer\Model\ImportStepFactory $importStepFactory,
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepository,
        \MageSuite\Importer\Model\Command\KillIndexers $killIndexers
    )
    {
        $this->importFactory = $importFactory;
        $this->importStepFactory = $importStepFactory;
        $this->importRepository = $importRepository;
        $this->killIndexers = $killIndexers;
    }

    public function scheduleImport($importIdentifier)
    {
        $configuration = $this->importRepository->getConfigurationById($importIdentifier);

        /** @var \MageSuite\Importer\Model\Import $import */
        $import = $this->importFactory->create();

        $import->setHash(uniqid());
        $import->setImportIdentifier($importIdentifier);
        $import->save();


        foreach ($configuration['steps'] as $identifier => $step) {
            $importStep = $this->importStepFactory->create();
            $importStep->setImportId($import->getId());
            $importStep->setIdentifier($identifier);

            $importStep->save();
        }

        if ($this->shouldKillIndexers($configuration)) {
            $this->killIndexers->execute();
        }
    }

    protected function shouldKillIndexers($configuration)
    {
        if (isset($configuration['kill_indexers_when_scheduled']) && $configuration['kill_indexers_when_scheduled'] == true) {
            return true;
        }

        return false;
    }
}