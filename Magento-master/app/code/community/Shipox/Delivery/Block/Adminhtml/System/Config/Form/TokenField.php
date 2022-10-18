<?php

/**
 * @category   Shipox -  Token Field Block
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/
class Shipox_Delivery_Block_Adminhtml_System_Config_Form_TokenField extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml($element)
    {
        $element->setDisabled('disabled');

        return parent::_getElementHtml($element);
    }
}
