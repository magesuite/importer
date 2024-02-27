<?php

namespace MageSuite\Importer\Model\Import\Adapter;

class FileAdapterFactory
{
    protected $objectManager = null;
    protected $instanceName = null;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \MageSuite\Importer\Model\Import\Adapter\FileAdapter::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @param array $data
     * @return \MageSuite\Importer\Model\Import\Adapter\FileAdapter
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
