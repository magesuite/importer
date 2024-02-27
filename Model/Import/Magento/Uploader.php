<?php

namespace MageSuite\Importer\Model\Import\Magento;

// phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
class Uploader extends \Magento\CatalogImportExport\Model\Import\Uploader
{
    /**
     * Optimized version of import file upload
     * Checking file contents validity was removed
     * @throws \Exception
     */
    protected function _validateFile()
    {
        $filePath = $this->_file['tmp_name'];

        if ($this->_directory->isReadable($filePath)) {
            $this->_fileExists = true;
        } else {
            $this->_fileExists = false;
        }

        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (!$this->checkAllowedExtension($fileExtension)) {
            throw new \Exception('Disallowed file type.'); // phpcs:ignore
        }
    }

    /**
     * Proceed moving a file from TMP to destination folder
     *
     * @param string $fileName
     * @param bool $renameFileOff
     * @return array
     */
    public function move($fileName, $renameFileOff = false)
    {
        if ($renameFileOff) {
            $this->setAllowRenameFiles(false);
        }
        if (preg_match('/\bhttps?:\/\//i', $fileName, $matches)) {
            $url = str_replace($matches[0], '', $fileName);
            $read = $this->_readFactory->create($url, \Magento\Framework\Filesystem\DriverPool::HTTP);

            $fileName = $this->renameDownloadedFile($url);

            $this->_directory->writeFile(
                $this->_directory->getRelativePath($this->getTmpDir() . '/' . $fileName),
                $read->readAll()
            );
        }

        $filePath = $this->_directory->getRelativePath($this->getTmpDir() . '/' . $fileName);
        $this->_setUploadFile($filePath);
        $destDir = $this->_directory->getAbsolutePath($this->getDestDir());
        $result = $this->save($destDir);
        $result['name'] = self::getCorrectFileName($result['name']);

        return $result;
    }

    protected function renameDownloadedFile($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $splitedPath = explode("/", $path);

        return end($splitedPath);
    }
}
