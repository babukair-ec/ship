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

$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('shipox_delivery/shipox'),
        'active_order',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => false,
            'default' => 1,
            'comment' => 'Active/Cancelled Order'
        )
    );

$installer->endSetup();
