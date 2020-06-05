<?php
/**
 * GoBeep
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    GoBeep
 * @package     Gobeep_Ecommerce
 * @author      Jonathan Gautheron <jgautheron@gobeep.co>
 * @copyright   Copyright (c) GoBeep (https://gobeep.co)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Gobeep\Ecommerce\Setup;

use Gobeep\Ecommerce\SdkInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Gobeep\Ecommerce\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('gobeep_ecommerce_refund')
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            100,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Order id'
        )->addColumn(
            'order_increment_id',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Order Increment ID'
        )->addColumn(
            'status',
            Table::TYPE_TEXT,
            20,
            ['nullable' => false, 'default' => SdkInterface::STATUS_PENDING],
            'Status'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Creation date'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Last update date'
        )->addColumn(
            'refund_email_sent',
            Table::TYPE_BOOLEAN,
            null,
            ['default' => false],
            'Refund email sent'
        )->addColumn(
            'winning_email_sent',
            Table::TYPE_BOOLEAN,
            null,
            ['default' => false],
            'Winning email sent'
        )->addIndex(
            $installer->getIdxName('gobeep_ecommerce_refund', 'order_id'),
            'order_id',
            AdapterInterface::INDEX_TYPE_UNIQUE
        )->addForeignKey(
            $installer->getFkName('gobeep_ecommerce_refund', 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Gobeep_Ecommerce refund table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
