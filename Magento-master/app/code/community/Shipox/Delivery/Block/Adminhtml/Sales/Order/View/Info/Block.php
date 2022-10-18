<?php

/**
 * @category   Shipox -  Order View
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/
class Shipox_Delivery_Block_Adminhtml_Sales_Order_View_Info_Block extends Mage_Adminhtml_Block_Widget
{

    /**
     * @return string
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * @param $order
     * @return mixed
     */
    public function isShipoxOrderCreated($order)
    {
        return Mage::getModel("shipox_delivery/tracking")->getOrderTrackingData($order);
    }


    /**
     * @param $status
     * @return bool
     */
    public function showShipoxOrderCancelField($status) {
        $shipoxHelper = new Shipox_Delivery_Helper_Data();
        return $shipoxHelper->isWingOrderCanReject($status);
    }

    /**
     * @param $order
     * @return array
     */
    public function getProperPackagesForOrder($order)
    {
        $shipoxHelper = new Shipox_Delivery_Helper_Data();
        return $shipoxHelper->getProperPackagesForOrder($order);
    }

    /**
     * @param $shipoxOrderId
     * @return null
     */
    public function getShipoxOrderDetailInfo($shipoxOrderId, $order) {
        $shipoxHelper = new Shipox_Delivery_Helper_Data();
        $statusMapping = new Shipox_Delivery_Model_Statusmapping();
        $shipoxOrderData =  $shipoxHelper->getShipoxOrderDetails($shipoxOrderId, true, $order);

        if($shipoxOrderData) {
            $shipoxOrderData['status_object'] = $statusMapping->getOrderStatus($shipoxOrderData['status']);
            $shipoxOrderData['estimated_delivery_date'] = date('Y-m-d H:i:s', strtotime($shipoxOrderData['deadline_time']));
        }

        return $shipoxOrderData;
    }

    /**
     * @param $order
     * @return bool
     */
    public function isShipoxOrderCreatedFromFrontEnd($order) {
        $shippingMethod = $order->getShippingMethod();
        $shippingMethodArray = explode("_", $shippingMethod);

        if($shippingMethodArray[0] != Mage::getStoreConfig('carriers/shipox/alias'))
            return false;

        return true;
    }


    /**
     * @param $shipoxOrderId
     * @return null
     */
    public function getOrderAirWayBill($shipoxOrderId) {
        $shipoxHelper = new Shipox_Delivery_Helper_Data();
        return $shipoxHelper->getWingOrderAirWayBill($shipoxOrderId);
    }
}