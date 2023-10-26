<?php

declare(strict_types=1);

namespace MageSuite\Importer\Plugin\ServerStatusLogger\Model\StatusResolver;

class DisableMviewState
{
    protected \MageSuite\Importer\Helper\Config $configuration;

    public function __construct(\MageSuite\Importer\Helper\Config $configuration)
    {
        $this->configuration = $configuration;
    }

    public function aroundGetCurrentStatus(
        \MageSuite\ServerStatusLogger\Model\StatusResolver\MviewState $subject,
        callable $proceed
    ): array {
        if(!$this->configuration->isIndexerEnabled()) {
            return [];
        }

        return $proceed();
    }
}
