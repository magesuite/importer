<?php

namespace MageSuite\Importer\Test\Integration\Plugin;

class DisableIndexerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Importer\Plugin\DisableIndexer
     */
    protected $plugin;

    /**
     * @var \Magento\Indexer\Model\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerProcessorDummy;

    public function setUp() {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->plugin = $this->objectManager->get(\MageSuite\Importer\Plugin\DisableIndexer::class);

        $this->indexerProcessorDummy = $this->getMockBuilder(\Magento\Indexer\Model\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @magentoAdminConfigFixture indexer/indexing/enabled 1
     */
    public function testAroundUpdateMviewIndexerIsEnabled() {
        $wasCalled = false;

        $this->plugin->aroundUpdateMview($this->indexerProcessorDummy, function() use(&$wasCalled) {
            $wasCalled = true;
        });

        $this->assertTrue($wasCalled);
    }

    /**
     * @magentoDbIsolation disabled
     * @expectedException \Exception
     * @magentoDataFixture disableIndexerFixture
     */
    public function testAroundUpdateMviewIndexerIsDisabled() {
        $this->plugin->aroundUpdateMview($this->indexerProcessorDummy, function() {});
    }

    /**
     * @magentoAdminConfigFixture indexer/indexing/enabled 1
     */
    public function testReindexAllInvalidIndexerIsEnabled() {
        $wasCalled = false;

        $this->plugin->aroundReindexAllInvalid($this->indexerProcessorDummy, function() use(&$wasCalled) {
            $wasCalled = true;
        });

        $this->assertTrue($wasCalled);
    }

    /**
     * @magentoDbIsolation disabled
     * @expectedException \Exception
     * @magentoDataFixture disableIndexerFixture
     */
    public function testReindexAllInvalidIndexerIsDisabled() {
        $this->plugin->aroundReindexAllInvalid($this->indexerProcessorDummy, function() {});
    }

    public static function disableIndexerFixture()
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $configWriter = $objectManager->get(\Magento\Framework\App\Config\Storage\WriterInterface::class);

        $configWriter->save(\MageSuite\Importer\Plugin\DisableIndexer::INDEXER_ENABLED_XML_PATH, '0');
    }

    public static function disableIndexerFixtureRollback()
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $configWriter = $objectManager->get(\Magento\Framework\App\Config\Storage\WriterInterface::class);

        $configWriter->save(\MageSuite\Importer\Plugin\DisableIndexer::INDEXER_ENABLED_XML_PATH, '1');
    }
}