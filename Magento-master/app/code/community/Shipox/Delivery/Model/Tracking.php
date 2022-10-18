<?php

/**
 * @category   Shipox -  Tracking Model
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/

class Shipox_Delivery_Model_Tracking
{
    /**
     * @param $orderId
     * @return mixed
     */
    public function getOrderTrackingDataByIncrementId($orderId) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        return $this->getOrderTrackingData($order);
    }

    /**
     * @param $order
     * @return null
     */
    public function getOrderTrackingData($order) {
        $dbClient = new Shipox_Delivery_Helper_Dbclient();

        if(isset($order)) {
            $shipoxOrderData = $dbClient->getData($order->getQuoteId(), $order->getEntityId());
            if($shipoxOrderData && $shipoxOrderData->getOrderId() && $shipoxOrderData->getIsCompleted()) {
                return $shipoxOrderData->getData();
            }
        }

        return null;
    }
}