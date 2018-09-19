<?php

namespace MageSuite\Importer\Setup;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function upgrade(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.2') < 0) {

            $tableName = $setup->getTable('import_log');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $columns = [
                    'status' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => false,
                        'size' => 1,
                        'comment' => 'Import status',
                        'default' => 1
                    ]
                ];

                $connection = $setup->getConnection();

                foreach ($columns as $name => $definition) {
                    if($connection->tableColumnExists($tableName, $name)){
                        $connection->addColumn($tableName, $name, $definition);
                    }
                }

            }
        }

        if (version_compare($context->getVersion(), '0.0.3') < 0) {

            $tableName = $setup->getTable('import_log');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $columns = [
                    'import_identifier' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => false,
                        'size' => '255',
                        'comment' => 'Import identifier'
                    ]
                ];

                $connection = $setup->getConnection();

                foreach ($columns as $name => $definition) {
                    if($connection->tableColumnExists($tableName, $name)) {
                        $connection->addColumn($tableName, $name, $definition);
                    }
                }

            }
        }

        if (version_compare($context->getVersion(), '0.0.5') < 0) {
            if (!$setup->tableExists('images_metadata')) {
                $table = $setup->getConnection()->newTable($setup->getTable('images_metadata'));

                $table->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Image id'
                )
                    ->addColumn(
                        'path',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        '255',
                        ['nullable => false'],
                        'Image path'
                    )
                    ->addColumn(
                        'size',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [],
                        'Image size'
                    )
                    ->addIndex(
                        $setup->getIdxName(
                            $setup->getTable('images_metadata'),
                            ['path'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['path'],
                        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Images metadata table');

                $setup->getConnection()->createTable($table);
            }
        }

        $setup->endSetup();
    }
}