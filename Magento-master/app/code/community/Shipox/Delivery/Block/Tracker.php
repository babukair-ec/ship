<?php

/**
 * @category   Shipox -  Tracker Block
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/
class Shipox_Delivery_Block_Tracker extends Mage_Core_Block_Template
{
    /**
     * @return mixed
     */
    public function trackOrder()
    {
        $helper = new Shipox_Delivery_Helper_Data();

        $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $shipoxOrder = Mage::getModel("shipox_delivery/tracking")->getOrderTrackingDataByIncrementId($orderId);

        if($shipoxOrder) {
            $shipoxOrder['shipox_order_url'] = $helper->getWingSiteURL().Mage::getStoreConfig('tracking/config/path').$shipoxOrder['wing_order_number'];
        }

        return $shipoxOrder ;
    }
}