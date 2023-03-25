<?php

namespace MageSuite\Importer\Controller\Adminhtml\Import;

class Show extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected \Magento\Framework\Registry $registry;
    protected \MageSuite\Importer\Api\ImportRepositoryInterface $importRepositoryInterface;
    protected \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \MageSuite\Importer\Api\ImportRepositoryInterface $importRepositoryInterface,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->importRepositoryInterface = $importRepositoryInterface;
        $this->redirectFactory = $redirectFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $importId = $this->getRequest()->getParam('id', 0);
        $steps = $this->importRepositoryInterface->getStepsByImportId($importId);
        $this->registry->register('import_steps', $steps);
        $resultPage->getConfig()->getTitle()->prepend(__('Import Log'));

        if (empty($steps)) {
            $redirect = $this->redirectFactory->create();
            $redirect->setPath('*/*/index');
            return $redirect;
        }

        return $resultPage;
    }
}
