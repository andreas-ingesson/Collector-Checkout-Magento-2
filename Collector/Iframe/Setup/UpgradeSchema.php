<?php

namespace Collector\Iframe\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.1.8') < 0) {
            $tableName = $setup->getTable('collector_anti_fraud');
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'is_anti_fraud',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false
                    ],
                    'Anti fraud'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Status'
                )
                ->setComment('Anti fraud')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.1.10') < 0) {
            $tableName = $setup->getTable('collector_anti_fraud');

            $setup->getConnection()->addColumn(
                $tableName,
                'increment_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'default' => '',
                    'nullable' => false,
                    'comment' => 'Increment ID'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.12') < 0) {
            $tableName = $setup->getTable('collector_order_checker');
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'increment_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Increment'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->setComment('Anti fraud')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $setup->getConnection()->createTable($table);
        }
        
        if (version_compare($context->getVersion(), '1.2.0') < 0) {
            $table = $setup->getTable('quote');
            $columns = [
                'collector_public_token' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Collector Public Token',
                ],
                'newsletter_signup' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '255',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Collector Newsletter Signup',
                ],
                'is_iframe' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '255',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Collector Is Iframe',
                ],
            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($table, $name, $definition);
            }
            
            $table = $setup->getTable('sales_order');
            
            $columns = [
                'is_iframe' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '255',
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Collector Is Iframe',
                ],
            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($table, $name, $definition);
            }
        }
        
        if (version_compare($context->getVersion(), '2.0.0') < 0) {
            $table = $setup->getTable('quote');
            
            $columns = [
                'shown_success_page' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Collector has shown success page',
                ],
            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($table, $name, $definition);
            }
        }
        $setup->endSetup();
    }
}
