<?php

namespace MageSuite\Importer\Services\Notification;

class EmailSender
{
    public const TEMPLATE_IDENTIFIER = 'import_error_notification';

    protected \MageSuite\Importer\Helper\Config $config;

    protected \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder;

    public function __construct(
        \MageSuite\Importer\Helper\Config $config,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->config = $config;
        $this->transportBuilder = $transportBuilder;
    }

    public function notify($error, $stepIdentifier = null): void
    {
        $storeAdminName = $this->config->getAdminNotificationSenderName();
        $storeAdminEmails = $this->config->getAdminNotificationEmails();

        if ($stepIdentifier instanceof \MageSuite\Importer\Model\ImportStep) {
            $stepIdentifier = $stepIdentifier->getData('identifier');
        }

        if (empty($storeAdminEmails)) {
            return;
        }

        if (is_array($error)) {
            $error = $this->prepareMessage($error);
        }

        $this->getTransport(
            [
                'name' => $storeAdminName,
                'email' => array_shift($storeAdminEmails),
            ],
            [
                'error' => $error,
                'step_identifier' => $stepIdentifier
            ],
            $storeAdminEmails
        )->sendMessage();
    }

    protected function getTransport($sender, $templateVariables, $storeAdminEmails): \Magento\Framework\Mail\TransportInterface
    {
        $transport = $this->transportBuilder->setTemplateIdentifier(self::TEMPLATE_IDENTIFIER)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_ADMINHTML, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
            ->setTemplateVars($templateVariables)
            ->setFrom($sender)
            ->addTo($sender['email'])
            ->setReplyTo($sender['email']);

        if (count($storeAdminEmails)) {
            foreach ($storeAdminEmails as $email) {
                $transport->addBcc($email);
            }
        }

        return $transport->getTransport();
    }

    protected function prepareMessage(array $errors): string
    {
        $resultErrorMessage = '';
        /** @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError $error */
        foreach ($errors as $error) {
            $resultErrorMessage .= 'Row #' . $error->getRowNumber() . ' - Error:  ' . $error->getErrorMessage() . '|';
        }

        return $resultErrorMessage;
    }
}
