<?php

namespace MageSuite\Importer\Test\Integration\Services\Import;

class ImageManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\Importer\Services\Import\ImageManager
     */
    protected $imageManager;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->imageManager = $this->objectManager->create(\MageSuite\Importer\Services\Import\ImageManager::class);

        $resource = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $this->connection = $resource->getConnection();

        $this->setUploadedImages();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testItProperlyGetsInformationIfImageWasUploaded()
    {
        $this->assertTrue($this->imageManager->wasImagePreviouslyUploaded('uploaded_image.png', 100));
        $this->assertFalse($this->imageManager->wasImagePreviouslyUploaded('uploaded_image.png', 200));
        $this->assertFalse($this->imageManager->wasImagePreviouslyUploaded('not_uploaded_image.png', 100));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testItProperlyAddsInformationAboutUploadedImage() {
        $this->imageManager->insertImageMetadata('new_uploaded_image.png', 200);
        $this->assertTrue($this->imageManager->wasImagePreviouslyUploaded('new_uploaded_image.png', 200));
        $this->assertFalse($this->imageManager->wasImagePreviouslyUploaded('new_uploaded_image.png', 100));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testItProperlyUpdatesInformationAboutUploadedImage() {
        $this->assertTrue($this->imageManager->wasImagePreviouslyUploaded('uploaded_image.png', 100));
        $this->imageManager->insertImageMetadata('uploaded_image.png', 200);
        $this->imageManager->resetUploadedImagesData();
        $this->assertTrue($this->imageManager->wasImagePreviouslyUploaded('uploaded_image.png', 200));
    }

    public function setUploadedImages() {
        $this->connection->insert('images_metadata', ['path' => 'uploaded_image.png', 'size' => '100']);
    }
}