<?php

namespace MageSuite\Importer\Services\Notification;

class EmailSender
{
    const TEMPLATE_IDENTIFIER = 'import_error_notification';

    /**
     * @var \MageSuite\Importer\Helper\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    public function __construct(
        \MageSuite\Importer\Helper\Config $config,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->config = $config;
        $this->transportBuilder = $transportBuilder;
    }

    public function notify($error, $stepIdentifier = '')
    {
        $storeAdminName = $this->config->getAdminNotificationSenderName();
        $storeAdminEmails = $this->config->getAdminNotificationEmails();

        if (!$storeAdminEmails) {
            return;
        }

        $this->getTransport([
                'name' => $storeAdminName,
                'email' => array_shift($storeAdminEmails),
            ],
            ['error' => $error, 'step_identifier' => $stepIdentifier],
            $storeAdminEmails
        )->sendMessage();
    }

    protected function getTransport($sender, $templateVariables, $storeAdminEmails)
    {
        $transport = $this->transportBuilder->setTemplateIdentifier(self::TEMPLATE_IDENTIFIER)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_ADMINHTML, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
            ->setTemplateVars(['data' => $templateVariables])
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
}
