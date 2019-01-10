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

        if (!$this->validateData($filePath) and
            (
                $this->importModel->getErrorAggregator()->getValidationStrategy() == \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR and
                $this->importModel->getErrorAggregator()->hasFatalExceptions()
            )
        ) {
            $message = $this->getLogTrace() . PHP_EOL;

            throw new \Exception($message);
        }

        $this->importData();

        if ($this->importModel->getErrorAggregator()->hasToBeTerminated()) {
            $this->importModel->addLogComment($this->getErrorMessages());
        }

        if (
            $this->importModel->getErrorAggregator()->getValidationStrategy() == \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS and
            $this->importModel->getErrorAggregator()->hasFatalExceptions()
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
}