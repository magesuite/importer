<?php

namespace MageSuite\Importer\Model;

class FileImporter extends \FireGento\FastSimpleImport\Model\Importer
{
    /**
     * @var \Magento\ImportExport\Model\Import
     */
    private $importModel;

    public function processImport($filePath)
    {
        $this->importModel = $this->createImportModel();

        if (!$this->validateData($filePath) AND $this->importModel->getErrorAggregator()->hasToBeTerminated()) {
            $message = $this->getLogTrace() . PHP_EOL;
            $message .= $this->getErrorMessage();

            throw new \Exception($message);
        }

        $this->importData();

        if($this->importModel->getErrorAggregator()->hasToBeTerminated()) {
            $this->importModel->addLogComment($this->getErrorMessages());
        }

        return $this->importModel->getFormatedLogTrace();
    }

    public function validateData($filePath)
    {
        $source = $this->importAdapterFactory->create(array('filePath' => $filePath));
        $this->validationResult = $this->importModel->validateSource($source);
        $this->addToLogTrace($this->importModel);

        return $this->validationResult;
    }

    protected function importData()
    {
        $this->importModel->importSource();
        $this->_handleImportResult($this->importModel);
    }

    /**
     * @param $message
     * @return string
     */
    private function getErrorMessage()
    {
        $message = '';

        $errors = $this->importModel->getErrorAggregator()->getAllErrors();

        foreach ($errors as $error) {
            $message .= $error->getErrorMessage() . ': ' . $error->getErrorDescription() . PHP_EOL;
        }

        return $message;
    }
}