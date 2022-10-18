<?php

/**
 * @category   Shipox -  Vehicle Type
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/
class Shipox_Delivery_Model_Vehicletype
{
    /**
     * @return array
     */
    public function toValueArray()
    {
        $result = array();
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $result[] = $option['value'];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'bike', 'label' => Mage::helper('shipping')->__('Bike')),
            array('value' => 'sedan', 'label' => Mage::helper('shipping')->__('Sedan')),
            array('value' => 'minivan', 'label' => Mage::helper('shipping')->__('Minivan')),
            array('value' => 'panelvan', 'label' => Mage::helper('shipping')->__('Panel Van')),
            array('value' => 'light_truck', 'label' => Mage::helper('shipping')->__('Light Truck')),
            array('value' => 'refrigerated_truck', 'label' => Mage::helper('shipping')->__('Refrigerated Truck')),
            array('value' => 'towing', 'label' => Mage::helper('shipping')->__('Towing')),
            array('value' => 'relocation', 'label' => Mage::helper('shipping')->__('Relocation')),
        );
    }
}