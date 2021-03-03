<?php

namespace MageSuite\Importer\Controller\Adminhtml\Import;

class ShowServerStatusLog extends \Magento\Backend\App\Action
{
    /**
     * @var \MageSuite\Importer\Model\ImportStepFactory
     */
    protected $importStepFactory;

    /**
     * @var \MageSuite\ServerStatusLogger\Model\RenderLogData
     */
    protected $renderLogData;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $rawResultFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MageSuite\Importer\Model\ImportStepFactory $importStepFactory,
        \MageSuite\ServerStatusLogger\Model\RenderLogData $renderLogData,
        \Magento\Framework\Controller\Result\RawFactory $rawResultFactory
    ) {
        parent::__construct($context);

        $this->importStepFactory = $importStepFactory;
        $this->renderLogData = $renderLogData;
        $this->rawResultFactory = $rawResultFactory;
    }

    public function execute()
    {
        $result = $this->rawResultFactory->create();

        $step = $this->importStepFactory->create();
        $step->load($this->_request->getParam('step_id'), 'id');

        $logData = json_decode($step->getServerStatusLog(), true);

        if(empty($logData)) {
            return $result;
        }

        $output = new \Symfony\Component\Console\Output\BufferedOutput();

        foreach($logData as $attempt => $serverStatusLog) {
            $output->writeln('Attempt '.$attempt);
            $output->writeln(str_repeat('-', 100));

            $this->renderLogData->execute($output, $serverStatusLog);

            $output->writeln('');
        }

       $result->setContents(sprintf('<pre>%s</pre>', $output->fetch()));

       return $result;
    }
}
