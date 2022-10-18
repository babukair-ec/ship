<?php

/**
 * @category   Shipox -  Observer
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/

class Shipox_Delivery_Model_Observer
{
    protected $_code = 'shipox';
    protected $_logFile = 'shipox_observer.log';

    /**
     * @param Varien_Event_Observer $observer
     */
    public function paymentMethodIsActive(Varien_Event_Observer $observer)
    {
//        Mage::log('Payment Method Is Active Event Triggered: '.print_r($observer->debug(), true), null, $this->_logFile);
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function pushOrderAfterShipmentCreation(Varien_Event_Observer $observer)
    {

        $shipoxDBClient = new Shipox_Delivery_Helper_Dbclient();
        $shipoxHelper = new Shipox_Delivery_Helper_Data();
        $shipoxCarrier = new Shipox_Delivery_Model_Carrier();

        $order = $observer->getEvent()->getOrder();

        if ($order->getExportProcessed()) { //check if flag is already set.
            return $this;
        }

        $order->setExportProcessed(true);

        $quoteId = $order->getQuoteId();
        $shippingMethod = $order->getShippingMethod();
        $shippingMethodArray = explode("_", $shippingMethod);
        $shipoxOrderModel = $shipoxDBClient->getData($quoteId);

        if ($shippingMethodArray[0] == $this->_code) {
            $menuId = $shipoxOrderModel->getWingMenuId();
            $customerLatLonAddress = $shipoxHelper->extractLatLonArrayFromString($shipoxOrderModel->getDestinationLatlon());

            if ($menuId && $customerLatLonAddress) {
                $courierItem = $shipoxHelper->getModelItemIfExists($shippingMethod, 'courier');
                $vehicleItem = $shipoxHelper->getModelItemIfExists($shippingMethod, 'vehicle');

                if ($courierItem && $vehicleItem) {

                    $additionalRequest = array(
                        'courier_type' => $courierItem,
                        'vehicle_type' => $vehicleItem
                    );

                    $packageId = $shipoxHelper->getPackagesPricesList($menuId, $customerLatLonAddress, $additionalRequest, true);

                    if ($packageId) {

                        $responseOrder = $shipoxHelper->pushShipoxOrder($order, $packageId, $customerLatLonAddress, $shipoxOrderModel->getData());

                        if (!empty($responseOrder)) {
                            $shipoxOrderModel->setWingPackageId($packageId);
                            $shipoxOrderModel->setWingOrderId($responseOrder['id']);
                            $shipoxOrderModel->setOrderId($order->getId());
                            $shipoxOrderModel->setWingOrderNumber($responseOrder['order_number']);
                            $shipoxOrderModel->setWingOrderStatus($responseOrder['order_status']);
                            $shipoxOrderModel->setCompletedAt(Varien_Date::now());
                            $shipoxOrderModel->setIsCompleted(1);
                            $shipoxOrderModel->setActiveOrder(1);

                            $shipoxOrderModel->save();

                            $itemsQuantity = $shipoxHelper->generateProductQuantityArray($order);

                            if(Mage::getStoreConfig('carriers/shipox/is_create_shipment')) {
                                $shipoxCarrier->setShipmentAndTrackingNumberOnShipment($order, $itemsQuantity, $responseOrder['order_number']);
                            }
//
//                            $shipoxCarrier->setShipmentAndTrackingNumberOnInvoice($order, $itemsQuantity);

                        }
                    }
                }
            }
        }

        return $this;
    }


    /**
     * @param Varien_Event_Observer $observer
     */
    public function customerOrderShipoxTrackerBlock(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();

        if (($block->getNameInLayout() == 'sales.order.info') && ($child = $block->getChild('shipox.order.info.customer'))) {
            $transport = $observer->getTransport();
            if ($transport) {
                $html = $transport->getHtml();
                $html .= $child->toHtml();
                $transport->setHtml($html);
            }
        }
    }


    /**
     * Order info block for Admin Panel
     * @param Varien_Event_Observer $observer
     */
    public function getSalesOrderViewInfo(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();

        if (($block->getNameInLayout() == 'order_info') && ($child = $block->getChild('shipox.order.info.block'))) {
            $transport = $observer->getTransport();
            if ($transport) {
                $html = $transport->getHtml();
                $html .= $child->toHtml();
                $transport->setHtml($html);
            }
        }

        if (($block->getNameInLayout() == 'order_info') && ($child = $block->getChild('shipox.order.shipment.info.block'))) {
            $transport = $observer->getTransport();
            if ($transport) {
                $html = $transport->getHtml();
                $html .= $child->toHtml();
                $transport->setHtml($html);
            }
        }
    }


    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function getOrderWhenCancelled(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            return $this;
        }

        $oldStatus = $order->getOrigData('status');
        $newStatus = $order->getStatus();

        if(($oldStatus != $newStatus) && ($newStatus == 'canceled')) {
            $shipoxHelper = new Shipox_Delivery_Helper_Data();

            $order = $observer->getEvent()->getOrder();
            $shipoxHelper->cancelWingOrder($order, 'Magento Order cancelled event fired. Merchant cancelling the Order');
        }
        return $this;
    }
}