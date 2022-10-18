<?php
/**
 * @category   Wing Delivery Setup
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/


/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;

/**
 * Creating table shipox_delivery
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('shipox_delivery/shipox'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'auto_increment' => true,
        'unsigned' => true,
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Entity id')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Quote ID')
    ->addColumn('wing_menu_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default' => 0
    ), 'Wing Menu Id')
    ->addColumn('wing_package_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default' => 0
    ), 'Wing Package Id')
    ->addColumn('wing_order_id', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable' => true,
    ), 'Wing Order Id')
    ->addColumn('wing_order_number', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable' => true,
    ), 'Wing Order Number')
    ->addColumn('wing_order_status', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable' => true,
    ), 'Wing Order Status')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable' => true,
    ), 'Order Id')
    ->addColumn('destination', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => true,
    ), 'Order Destination')
    ->addColumn('destination_latlon', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable' => true,
    ), 'Order Destination Lat Lon')
    ->addColumn("create_at", Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
        'default'  => null,
    ), "Created Qoute At")
    ->addColumn("completed_at", Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
        'default'  => null,
    ), "Completed At")
    ->addColumn('is_completed', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default' => 0
    ), 'Is Completed')
    ->addIndex($installer->getIdxName(
        $installer->getTable('shipox_delivery/shipox'),
        array('wing_order_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
    ),
        array('wing_order_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->setComment('Wing Order Items');

$installer->getConnection()->createTable($table);
