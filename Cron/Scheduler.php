<?php

namespace MageSuite\Importer\Cron;

class Scheduler
{
    protected \MageSuite\Importer\Services\Import\Scheduler $scheduler;

    public function __construct(\MageSuite\Importer\Services\Import\Scheduler $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    /**
     * etc/crontab.xml does not allow passing arguments to invoked methods
     * so for scheduling imports we use part of invoked method name as import identifier
     * calling scheduleProductsImport will invoke import with identifier: products_import
     * @param $name
     * @param array $arguments
     */
    public function __call($name, array $arguments)
    {
        if (preg_match('/schedule([a-zA-Z+])/', $name)) {
            $importIdentifier = strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $name));
            $importIdentifier = str_replace('schedule_', '', $importIdentifier);

            $this->scheduler->scheduleImport($importIdentifier);
        }
    }
}
