<?php
/**
 * @category   Wing
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/

class Shipox_Delivery_Model_Shipox extends Mage_Core_Model_Abstract
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('shipox_delivery/shipox');
    }

    /**
     * If object is new adds creation date
     *
     * @return Shipox_Delivery_Model_Shipox
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->isObjectNew()) {
            $this->setData('create_at', Varien_Date::now());
        }
        return $this;
    }
}