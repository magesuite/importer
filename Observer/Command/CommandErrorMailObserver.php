<?php

namespace MageSuite\Importer\Observer\Command;

class CommandErrorMailObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Store\Model\ScopeInterface
     */
    private $config;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    )
    {
        $this->config = $config;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \MageSuite\Importer\Model\ImportStep $step */
        $step = $observer->getData('step');
        $error = $observer->getData('error');

        $storeAdminName = $this->config->getValue('trans_email/importer_email/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $storeAdminEmail = $this->config->getValue('trans_email/importer_email/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($storeAdminEmail == null) {
            return;
        }

        $adminEmails = ($storeAdminEmail) ? array_map('trim', explode("\n", $storeAdminEmail)) : [$this->config->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)];

        $emailTemplateVariables = ['error' => $error, 'step_identifier' => $step->getIdentifier()];

        $templateVariables = new \Magento\Framework\DataObject();
        $templateVariables->setData($emailTemplateVariables);

        $sender = [
            'name' => $storeAdminName,
            'email' => array_shift($adminEmails),
        ];

        $transport = $this->transportBuilder->setTemplateIdentifier('import_error_notification')
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_ADMINHTML, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
            ->setTemplateVars(['data' => $templateVariables])
            ->setFrom($sender)
            ->addTo($sender['email'])
            ->setReplyTo($sender['email']);

        if(count($adminEmails)){
            foreach($adminEmails AS $email){
                $transport->addBcc($email);
            }
        }

        $transport->getTransport()->sendMessage();
    }
}