<?php

/**
 * @category   Shipox -  Get Token Block
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/
class Shipox_Delivery_Block_Adminhtml_System_Config_Form_GetTokenButton extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _construct()
    {
        parent::_construct();
        $template = $this->setTemplate('shipox/system/config/get_token_button.phtml');
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    public function getAjaxGetTokenUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/shipoxadmin/gettoken');
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
            array(
                'id'      => 'get_shipox_token',
                'label'   => $this->helper('adminhtml')->__('Get Token'),
                'onclick' => 'javascript:getJWTToken(); return false;'
            )
        );

        return $button->toHtml();
    }
}
