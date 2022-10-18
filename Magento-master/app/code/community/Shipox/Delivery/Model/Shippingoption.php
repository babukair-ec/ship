<?php

/**
 * @category   Shipox -  Shipping Options
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/

class Shipox_Delivery_Model_Shippingoption
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'bike_in_5_days', 'label' => Mage::helper('shipping')->__('Bike: Delivery: 1-4 working days')),
            array('value' => 'bike_next_day', 'label' => Mage::helper('shipping')->__('Bike: Delivery: within 24 hours')),
            array('value' => 'bike_same_day', 'label' => Mage::helper('shipping')->__('Bike: Delivery: within same day')),
            array('value' => 'bike_bullet', 'label' => Mage::helper('shipping')->__('Bike: Delivery: within 4 hours')),

            array('value' => 'sedan_in_5_days', 'label' => Mage::helper('shipping')->__('Sedan: Delivery: 1-4 working days')),
            array('value' => 'sedan_next_day', 'label' => Mage::helper('shipping')->__('Sedan: Delivery: within 24 hours')),
            array('value' => 'sedan_same_day', 'label' => Mage::helper('shipping')->__('Sedan: Delivery: within same day')),
            array('value' => 'sedan_bullet', 'label' => Mage::helper('shipping')->__('Sedan: Delivery: within 4 hours')),

            array('value' => 'minivan_in_5_days', 'label' => Mage::helper('shipping')->__('Small Van: Delivery: 1-4 working days')),
            array('value' => 'minivan_next_day', 'label' => Mage::helper('shipping')->__('Small Van: Delivery: within 24 hours')),
            array('value' => 'minivan_same_day', 'label' => Mage::helper('shipping')->__('Small Van: Delivery: within same day')),
            array('value' => 'minivan_bullet', 'label' => Mage::helper('shipping')->__('Small Van: Delivery: within 4 hours')),

            array('value' => 'panelvan_in_5_days', 'label' => Mage::helper('shipping')->__('Panel Van: Delivery: 1-4 working days')),
            array('value' => 'panelvan_next_day', 'label' => Mage::helper('shipping')->__('Panel Van: Delivery: within 24 hours')),
            array('value' => 'panelvan_same_day', 'label' => Mage::helper('shipping')->__('Panel Van: Delivery: within same day')),
            array('value' => 'panelvan_bullet', 'label' => Mage::helper('shipping')->__('Panel Van: Delivery: within 4 hours')),

            array('value' => 'light_truck_in_5_days', 'label' => Mage::helper('shipping')->__('International: Delivery: 1-4 working days')),
            array('value' => 'light_truck_next_day', 'label' => Mage::helper('shipping')->__('International: Delivery: within 24 hours')),
            array('value' => 'light_truck_same_day', 'label' => Mage::helper('shipping')->__('International: Delivery: within same day')),
            array('value' => 'light_truck_bullet', 'label' => Mage::helper('shipping')->__('International: Delivery: within 4 hours')),

            array('value' => 'refrigerated_truck_in_5_days', 'label' => Mage::helper('shipping')->__('Refrigerated Truck: Delivery: 1-4 working days')),
            array('value' => 'refrigerated_truck_next_day', 'label' => Mage::helper('shipping')->__('Refrigerated Truck: Delivery: within 24 hours')),
            array('value' => 'refrigerated_truck_same_day', 'label' => Mage::helper('shipping')->__('Refrigerated Truck: Delivery: within same day')),
            array('value' => 'refrigerated_truck_bullet', 'label' => Mage::helper('shipping')->__('Refrigerated Truck: Delivery: within 4 hours')),

            array('value' => 'towing_in_5_days', 'label' => Mage::helper('shipping')->__('Towing: Delivery: 1-4 working days')),
            array('value' => 'towing_next_day', 'label' => Mage::helper('shipping')->__('Towing: Delivery: within 24 hours')),
            array('value' => 'towing_same_day', 'label' => Mage::helper('shipping')->__('Towing: Delivery: within same day')),
            array('value' => 'towing_bullet', 'label' => Mage::helper('shipping')->__('Towing: Delivery: within 4 hours')),

            array('value' => 'relocation_in_5_days', 'label' => Mage::helper('shipping')->__('Relocation: Delivery: 1-4 working days')),
            array('value' => 'relocation_next_day', 'label' => Mage::helper('shipping')->__('Relocation: Delivery: within 24 hours')),
            array('value' => 'relocation_same_day', 'label' => Mage::helper('shipping')->__('Relocation: Delivery: within same day')),
            array('value' => 'relocation_bullet', 'label' => Mage::helper('shipping')->__('Relocation: Delivery: within 4 hours')),
        );
    }
}