<?php

/**
 * @category   Shipox -  Carrier Type
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/

class Shipox_Delivery_Model_Carrier extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'shipox';
    protected $_result = null;
    protected $_logFile = 'shipox_carrier.log';

    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!Mage::getStoreConfig('carriers/' . $this->_code . '/active')) {
            return false;
        }

        $result = $this->getWingRates($request);
        return $result;
    }

    /**
     * @param $request
     * @return false|Mage_Core_Model_Abstract
     */
    public function getWingRates($request)
    {

        $shippingAddress = null;
        $priceArr = array();

        $shipoxHelper = new Shipox_Delivery_Helper_Data();
        $shipoxDBClient = new Shipox_Delivery_Helper_Dbclient();

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $packageWeight = $shipoxHelper->getPackageWeightForWing($request->getPackageWeight());

        if (!$shipoxHelper->isAllowedSystemCurrency())
            return false;

        $shippingAddress = $quote->getShippingAddress();

        if ($shippingAddress) {
            $countryId = $shipoxHelper->getCountryWingId($shippingAddress->getCountry());

            if (!$shipoxHelper->isInternationalAvailable($countryId))
                return false;

            if ($shipoxHelper->isAllowedCountry($countryId)) {
                {
                    $menuId = $shipoxHelper->getPackageType($packageWeight, $countryId);
                    $customerLatLonAddress = $shipoxHelper->getAddressLatLon($shippingAddress);

                    if (!empty($customerLatLonAddress)) {
                        $packagePrices = $shipoxHelper->getPackagesPricesList($menuId, $customerLatLonAddress);

                        if ($packagePrices) {

                            $data = array(
                                'quote_id' => $quote->getEntityId(),
                                'wing_menu_id' => $menuId,
                                'destination' => $shipoxHelper->getFullDestination($shippingAddress),
                                'destination_latlon' => $customerLatLonAddress['lat'] . "," . $customerLatLonAddress['lon']
                            );

                            if ($shipoxDBClient->insertData($data)) {
                                foreach ($packagePrices as $listItem) {
                                    $packages = $listItem['packages'];
                                    $name = $listItem['name'];
                                    $vehicle_type = $listItem['vehicle_type'];

                                    foreach ($packages as $packageItem) {
                                        $label = $packageItem['delivery_label'];
                                        $price = $packageItem['price']['total'];
                                        $method = $vehicle_type . "_" . $packageItem['courier_type'];

                                        $response['type'] = 'success';
                                        $priceArr[$method] = array('label' => $name . " - " . $label, 'amount' => $price);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($priceArr)) {
            $result = Mage::getModel('shipping/rate_result');

            foreach ($priceArr as $method => $values) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier($this->_code);
                $rate->setCarrierTitle(Mage::getStoreConfig('shipox_delivery/service/title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($values['label']);
                $rate->setPrice($values['amount']);
                $rate->setCost('0');
                $result->append($rate);
            }
            return $result;
        }
        return false;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array($this->_code => 'wing');
    }

    /**
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }


    /**
     * @param $tracking
     * @return false|Mage_Core_Model_Abstract
     */
    public function getTrackingInfo($tracking)
    {
        $helper = new Shipox_Delivery_Helper_Data();
        $track = Mage::getModel('shipping/tracking_result_status');
        $track->setUrl($helper->getWingSiteURL() . Mage::getStoreConfig('tracking/config/path') . $tracking)
            ->setTracking($tracking)
            ->setCarrierTitle($this->getConfigData('title'));
        return $track;
    }


    /**
     * @param $order
     * @param $itemsQuantity
     * @param $shipoxOrderNumber
     */
    public function setShipmentAndTrackingNumberOnShipment($order, $itemsQuantity, $shipoxOrderNumber)
    {
        if ($order->canShip()) {
            $shipmentId = Mage::getModel('sales/order_shipment_api')->create($order->getIncrementId(), $itemsQuantity, $this->getConfigData('title'), 1, 1);

            Mage::getModel('sales/order_shipment_api')
                ->addTrack($shipmentId, $this->_code, $this->getConfigData('title') . "(".Mage::getStoreConfig('shipox_delivery/service/site_short_url').")", $shipoxOrderNumber);
        }
    }

    public function setShipmentAndTrackingNumberOnShipmentV2($order, $shipoxOrderNumber) {

        $data = array();
        $data['carrier_code'] =  $this->_code;
        $data['title'] = $this->getConfigData('title') . "(".Mage::getStoreConfig('shipox_delivery/service/site_short_url').")";
        $data['number'] = $shipoxOrderNumber;


        if ($order->canShip()) {
            $convertor = Mage::getModel('sales/convert_order');
            $shipment = $convertor->toShipment($order);

            foreach ($order->getAllItems() as $orderItem) {

                if (!$orderItem->getQtyToShip()) {
                    continue;
                }
                if ($orderItem->getIsVirtual()) {
                    continue;
                }
                $item = $convertor->itemToShipmentItem($orderItem);
                $qty = $orderItem->getQtyToShip();
                $item->setQty($qty);
                $shipment->addItem($item);
            }

            $track = Mage::getModel('sales/order_shipment_track')->addData($data);
            $shipment->addTrack($track);

            $shipment->register();
            $shipment->setEmailSent(true);
            $shipment->getOrder()->setIsInProcess(true);

            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();
        } else {
            $shipment = $order->getShipmentsCollection()->getFirstItem();
            $track = Mage::getModel('sales/order_shipment_track')->addData($data);
            $shipment->addTrack($track);
            $shipment->save();
        }
    }


    /**
     * @param $order
     * @param $itemsQuantity
     */
    public function setShipmentAndTrackingNumberOnInvoice($order, $itemsQuantity)
    {

        if ($order->canInvoice()) {

            Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $itemsQuantity, $this->getConfigData('title'), false, false);
        }
    }
}