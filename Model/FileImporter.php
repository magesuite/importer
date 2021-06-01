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
        $errorAgregator = $this->importModel->getErrorAggregator();

        if (!$this->validateData($filePath) and
            (
                $this->getPrivateProperty($errorAgregator, 'validationStrategy') == \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR and
                $errorAgregator->hasFatalExceptions()
            )
        ) {
            $message = $this->getLogTrace() . PHP_EOL;

            throw new \Exception($message);
        }

        $this->importData();

        if ($errorAgregator->hasToBeTerminated()) {
            $this->importModel->addLogComment($this->getErrorMessages());
        }

        if (
            $this->getPrivateProperty($errorAgregator, 'validationStrategy') == \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS and
            $errorAgregator->hasFatalExceptions()
        ) {
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
    
    protected function getPrivateProperty($object, $propertyName)
    {
        $reflection = new \ReflectionClass($object);

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

}
