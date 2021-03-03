<?php

namespace MageSuite\Importer\Model\Command;

class KillIndexers
{
    const AMOUNT_OF_ATTEMPTS_TO_KILL_PROCESSES = 10;
    const DELAY_BETWEEN_ATTEMPTS = 5;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->connection = $resourceConnection->getConnection();
        $this->logger = $logger;
    }

    public function execute()
    {
        $this->connection->update(
            $this->connection->getTableName('mview_state'),
            ['status' => 'idle']
        );

        $this->connection->update(
            $this->connection->getTableName('cron_schedule'),
            [
                'status' => 'error',
                'messages' => 'Killed by importer'
            ],
            [
                'job_code IN (?)' => ['indexer_update_all_views', 'indexer_reindex_all_invalid'],
                'status = ?' => 'running'
            ]
        );

        for($attempt = 0; $attempt < self::AMOUNT_OF_ATTEMPTS_TO_KILL_PROCESSES; $attempt++) {
            $this->killAll('group=index');

            sleep(self::DELAY_BETWEEN_ATTEMPTS);
        }
    }

    protected function killAll($match)
    {
        $match = escapeshellarg($match);

        exec("ps x|grep $match|grep -v grep|awk '{print $1}'", $pids, $ret);

        if ($ret) {
            return '';
        }

        if (empty($pids)) {
            return '';
        }

        $this->logger->info(sprintf(
            'Found following PIDs of indexer processes: %s. Attempting to kill with -9',
            implode(', ', $pids)
        ));

        foreach ($pids as $pid) {
            if (preg_match('/^([0-9]+)/', $pid, $r)) {
                system('kill -9 ' . $r[1], $k);
            }
        }
    }
}
