<?php

namespace MageSuite\Importer\Test\Unit\Command\Import;

class MapImagesTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\Importer\Command\Import\MapImages $command = null;
    protected ?\PHPUnit\Framework\MockObject\MockObject $imageMapperStub = null;
    protected ?\Magento\Framework\Filesystem\Io\File $fileIo = null;
    protected string $assetsDirectoryRelativeToMainDirectory;
    protected string $assetsDirectory;

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->imageMapperStub = $this
            ->getMockBuilder(\MageSuite\Importer\Services\Import\ImageMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->addSharedInstance($this->imageMapperStub, \MageSuite\Importer\Services\Import\ImageMapper::class);
        $this->command = $objectManager->create(\MageSuite\Importer\Command\Import\MapImages::class);
        $this->fileIo = $objectManager->create(\Magento\Framework\Filesystem\Io\File::class);

        $this->assetsDirectory = realpath(__DIR__.'/../assets');
        $this->assetsDirectoryRelativeToMainDirectory = str_replace(
            BP . DIRECTORY_SEPARATOR,
            '',
            $this->assetsDirectory
        );
    }

    public function testItImplementsCommandInterface()
    {
        $this->assertInstanceOf(\MageSuite\Importer\Command\Command::class, $this->command);
    }

    public function testItProperlyMapsImages()
    {
        $importWithImagesFilePath = $this->assetsDirectory . DIRECTORY_SEPARATOR . 'import_file_with_images';

        if ($this->fileIo->fileExists($importWithImagesFilePath)) {
            $this->fileIo->rm($importWithImagesFilePath);
        }

        $this->imageMapperStub->method('getImagesByProductSku')
            ->with('SKU', $this->assetsDirectory)
            ->willReturn(['base_image' => 'SKU.jpg']);

        $this->command->execute([
            'source_path' => $this->assetsDirectoryRelativeToMainDirectory . DIRECTORY_SEPARATOR . 'import_file',
            'target_path' => $this->assetsDirectoryRelativeToMainDirectory . DIRECTORY_SEPARATOR . 'import_file_with_images',
            'images_directory_path' => $this->assetsDirectoryRelativeToMainDirectory
        ]);

        $targetFileContents = $this->fileIo->read(
            $this->assetsDirectory . DIRECTORY_SEPARATOR . 'import_file_with_images'
        );

        $this->assertEquals('{"sku":"SKU","base_image":"SKU.jpg"}', $targetFileContents);
    }
}
