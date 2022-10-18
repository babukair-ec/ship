<?php

/**
 * @category   Shipox -  Payment Types
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/

class Shipox_Delivery_Model_Paymenttype
{
    public function toValueArray()
    {
        $result = array();
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $result[] = $option['value'];
        }
        return $result;
    }

    public function toOptionArray()
    {
        return array(
            array('value' => 'cash', 'label' => 'Cash'),
            array('value' => 'credit_balance', 'label' => 'Credit Balance'),
            array('value' => 'paypal', 'label' => 'Online Payment'),
        );
    }
}