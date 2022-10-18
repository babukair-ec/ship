<?php

/**
 * @category   Shipox -  Courier Type
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/

class Shipox_Delivery_Model_Couriertype
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
            array('value' => 'bullet', 'label' => 'Urgent'),
            array('value' => 'same_day', 'label' => 'Same Day'),
            array('value' => 'next_day', 'label' => 'Next Day'),
            array('value' => 'in_5_days', 'label' => 'In 5 Days'),
        );
    }
}