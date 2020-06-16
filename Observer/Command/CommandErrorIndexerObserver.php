<?php

namespace MageSuite\Importer\Observer\Command;

class CommandErrorIndexerObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\Importer\Command\Magento\EnableIndexers
     */
    protected $enableIndexersCommand;

    public function __construct(
        \MageSuite\Importer\Command\Magento\EnableIndexers $enableIndexersCommand
    )
    {
        $this->enableIndexersCommand = $enableIndexersCommand;
    }

    /**
     * In case of an error on last attempt we need to be sure that indexers that were disabled are enabled
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $wasFinalAttempt = $observer->getData('was_final_attempt');

        if(!$wasFinalAttempt) {
            return;
        }

        $this->enableIndexersCommand->execute([]);
    }
}