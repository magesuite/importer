<?php

declare(strict_types=1);

namespace MageSuite\Importer\Model\Command;

/**
 * @method int getStatus()
 * @method \MageSuite\Importer\Model\Command\Output setStatus(int $status)
 * @method \MageSuite\Importer\Model\Command\Output setMessage(string $message)
 */
class Output extends \Magento\Framework\DataObject
{
    public function __construct(array $data = [])
    {
        $data['status'] = $data['status'] ?? \MageSuite\Importer\Model\ImportStep::STATUS_DONE;
        parent::__construct($data);
    }

    public function getMessage(): string
    {
        $message = $this->_getData('message');

        if (is_array($message)) {
            return implode(PHP_EOL, $message);
        }

        if (is_object($message)) {
            return '';
        }

        if (is_bool($message)) {
            return $message ? 'Success' : 'Failed';
        }

        return (string)$message;
    }

    public function __toString(): string
    {
        return $this->getMessage();
    }
}
