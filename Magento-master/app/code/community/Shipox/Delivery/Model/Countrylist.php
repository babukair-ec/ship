<?php

/**
 * @category   Shipox -  Country List
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/

class Shipox_Delivery_Model_Countrylist
{
    /**
     * @return array
     */
    public function toKeyArray()
    {
        $result = array();
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $request = new Shipox_Delivery_Helper_Client();

        $result = $request->getCountryList();

        $arr = array();

        foreach ($result as $country) {
            $arr[] = array('value' => $country['id'], 'label' => $country['name']);
        }

        return $arr;
    }
}
