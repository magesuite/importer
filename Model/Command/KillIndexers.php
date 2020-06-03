<?php

namespace MageSuite\Importer\Model\Command;

class KillIndexers
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    public function execute()
    {
        $this->killAll('group=index');

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
                'job_code IN (?)' => ['indexer_update_all_views','indexer_reindex_all_invalid'],
                'status = ?' => 'running'
            ]
        );
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

        foreach ($pids as $pid) {
            if (preg_match('/^([0-9]+)/', $pid, $r)) {
                system('kill -9 '. $r[1], $k);
            }
        }
    }
}
