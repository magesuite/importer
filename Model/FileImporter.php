<?php

namespace MageSuite\Importer\Model;

class FileImporter extends Importer
{
    protected \Magento\ImportExport\Model\Import $importModel;

    public function processImport($filePath)
    {
        $this->importModel = $this->createImportModel();
        $errorAggregator = $this->importModel->getErrorAggregator();
        $validationStrategy = $this->getPrivateProperty($errorAggregator, 'validationStrategy');
        $hasFatalExceptions = $errorAggregator->hasFatalExceptions();

        if (!$this->validateData($filePath) && $validationStrategy === $this->getValidationStrategyStopOnError() && $hasFatalExceptions) {
            $message = $this->getLogTrace() . PHP_EOL;
            throw new \Exception($message); // phpcs:ignore
        }

        $this->importData();
        $errorMessages = $this->getErrorMessages();

        if (!empty($errorMessages) && !($validationStrategy === $this->getValidationStrategySkipErrors() && $hasFatalExceptions)) {
            $this->importModel->addLogComment($errorMessages);
        }

        return $this->importModel->getFormatedLogTrace();
    }

    public function validateData($filePath)
    {
        $source = $this->importAdapterFactory->create(['filePath' => $filePath]);
        $this->validationResult = $this->importModel->validateSource($source);
        $this->addToLogTrace($this->importModel);

        return $this->validationResult;
    }

    protected function importData()
    {
        $this->importModel->importSource();
        $this->_handleImportResult($this->importModel);
    }

    protected function getErrorMessage()
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

    protected function getValidationStrategyStopOnError():string
    {
        return \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR;
    }

    protected function getValidationStrategySkipErrors():string
    {
        return \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS;
    }
}
