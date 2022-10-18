<?php

/**
 * @category   Shipox -  Expired Token Block
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/
class Shipox_Delivery_Block_Adminhtml_System_Config_Form_ExpiredToken extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $helper = Mage::helper('shipox_delivery');
        return '<label style="color: #FF0000; font-weight: bold">'.$helper->setExpiredDate(Mage::getStoreConfig('shipox_delivery/auth/token_time')).'</label>';
    }
}
