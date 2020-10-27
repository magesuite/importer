<?php

namespace MageSuite\Importer\Test\Integration\Command\Magento;

class DisableIndexersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Importer\Command\Magento\DisableIndexers
     */
    protected $command;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->command  = $this->objectManager->get(\MageSuite\Importer\Command\Magento\DisableIndexers::class);
        $this->scopeConfig = $this->objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
    }

    /**
     * @magentoAdminConfigFixture indexer/indexing/enabled 0
     **/
    public function testItDisablesIndexers() {
        $this->command->execute([]);

        $this->assertEquals('0', $this->scopeConfig->getValue('indexer/indexing/enabled'));
    }
}
