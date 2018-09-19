<?php

namespace MageSuite\Importer\Test\Integration\Command\Magento;

class EnableIndexersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Importer\Command\Magento\EnableIndexers
     */
    protected $command;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function setUp() {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->command  = $this->objectManager->get(\MageSuite\Importer\Command\Magento\EnableIndexers::class);
        $this->scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
    }

    /**
     * @magentoAdminConfigFixture indexer/indexing/enabled 1
     **/
    public function testItEnablesIndexers() {
        $this->command->execute([]);

        $this->assertEquals('1', $this->scopeConfig->getValue('indexer/indexing/enabled'));
    }
}