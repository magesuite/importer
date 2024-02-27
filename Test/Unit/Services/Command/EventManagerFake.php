<?php

namespace MageSuite\Importer\Test\Unit\Services\Command;

class EventManagerFake implements \Magento\Framework\Event\ManagerInterface
{
    protected array $dispatchedEvents = [];

    public function dispatch($eventName, array $data = [])
    {
        $this->dispatchedEvents[] = [
            'eventName' => $eventName,
            'data' => $data
        ];
    }

    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }
}
