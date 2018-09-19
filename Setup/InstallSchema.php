<?php

namespace MageSuite\Importer\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    /**
     * Installs DB schema for a module
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->tableExists('import_log')) {
            $table = $installer->getConnection()->newTable($installer->getTable('import_log'));

            $table->addColumn(
                'import_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
                'Import ID'
            )
                ->addColumn(
                    'hash',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    13,
                    ['nullable => false'],
                    'Hash'
                )
                ->addColumn(
                    'started_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Import started at'
                )
                ->addColumn(
                    'finished_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Import finished at'
                )
                ->addIndex(
                    $installer->getIdxName('import_log', ['hash']),
                    ['hash']
                )
                ->setComment('Import log table');

            $installer->getConnection()->createTable($table);

            $stepTable = $installer->getConnection()->newTable($installer->getTable('import_log_step'));

            $stepTable->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
                'Step id'
            )
            ->addColumn(
                'identifier',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Step identifier'
            )
            ->addColumn(
                'import_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Related import id'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => '1'],
                'Step status'
            )
            ->addColumn(
                'started_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Step started at'
            )
            ->addColumn(
                'finished_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Step finished at'
            )
            ->addColumn(
                'output',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => false],
                'Output'
            )
            ->addColumn(
                'error',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => false],
                'Error output'
            )
            ->setComment('Import step log table');

            $installer->getConnection()->createTable($table);
            $installer->getConnection()->createTable($stepTable);
        }

        $installer->endSetup();
    }
}