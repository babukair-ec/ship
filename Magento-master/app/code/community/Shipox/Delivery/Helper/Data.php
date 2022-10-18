<?php

/**
 * @category   Shipox -  Helper Data
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/
class Shipox_Delivery_Helper_Data extends Mage_Core_Helper_Data
{
    protected $_code = 'shipox';
    protected $_logFile = 'shipox_data.log';

    /**
     *  Get Wing Website url according to Environment
     */
    public function getWingSiteURL()
    {
        if (Mage::getStoreConfig('shipox_delivery/service/sandbox_flag') == 1) {
            return Mage::getStoreConfig('tracking/config/prelive_tracking_url');
        }
        return Mage::getStoreConfig('tracking/config/live_tracking_url');
    }

    /**
     * @param $timestamp
     * @return string
     */
    public function setExpiredDate($timestamp)
    {
        $date = date('Y-m-d', strtotime('+1 year', $timestamp));
        return $date;
    }

    /**
     * @param $weight
     * @return mixed
     */
    public function getPackageWeightForWing($weight)
    {
        $menuOption = Mage::getStoreConfig('carriers/' . $this->_code . '/default_weight');

        if ($menuOption == 0)
            return $weight;

        return $menuOption;
    }

    /**
     * @return bool
     */
    public function isAllowedSystemCurrency()
    {
        return (Mage::app()->getStore()->getCurrentCurrencyCode() == "AED") ? true : false;
    }

    /**
     * @param $countryCode
     * @return int
     */
    public function getCountryWingId($countryCode)
    {
        $request = new Shipox_Delivery_Helper_Client();
        $result = $request->getCountryList();
        $countryId = $this->getLocalCountryId();

        foreach ($result as $country) {
            if ($country['code'] == $countryCode) {
                $countryId = $country['id'];
                break;
            }
        }

        return $countryId;
    }

    /**
     * @return mixed
     */
    public function getLocalCountryId()
    {
        return Mage::getStoreConfig('carriers/' . $this->_code . '/base_country_id');
    }

    /**
     * @param $countryId
     * @return bool
     */
    public function isInternationalAvailable($countryId)
    {
        return $countryId == $this->getLocalCountryId() ? true : false;
    }

    /**
     * @param $countryId
     * @return bool
     */
    public function isAllowedCountry($countryId)
    {
        $allowedCountryList = explode(",", Mage::getStoreConfig('carriers/' . $this->_code . '/specificcountry'));
        if (in_array($countryId, $allowedCountryList)) {
            return true;
        }
        return false;
    }

    /**
     * @param int $totalWeight
     * @param $countryId
     * @return int
     */
    public function getPackageType($totalWeight = 0, $countryId)
    {
        $request = new Shipox_Delivery_Helper_Client();

        $requestPackage = array(
            "from_country_id" => $this->getLocalCountryId(),
            "to_country_id" => $countryId,
        );

        $result = $request->getPackageMenu('?' . http_build_query($requestPackage));

        foreach ($result['list'] as $package) {
            if ($package["weight"] >= $totalWeight) {
                return $package["menu_id"];
            }
        }

        return 0;
    }

    /**
     * @param $shippingAddress
     * @return array
     */
    public function getAddressLatLon($shippingAddress)
    {
        $responseArray = array();
        $shipoxApiClient = new Shipox_Delivery_Helper_Client();
        $shipoxGeoClient = new Shipox_Delivery_Helper_Geoclient();

        $city = $shippingAddress->getCity();
        $region = $shippingAddress->getRegion();

        if ($this->isUrgentEnabled()) {

            $street = $shippingAddress->getStreet();
            $geoLatLon = $shipoxGeoClient->getLatLon($street, $city, $region);

            if ($geoLatLon) {
                $responseArray['lat'] = $geoLatLon['lat'];
                $responseArray['lon'] = $geoLatLon['lng'];
            }

        }

        if (empty($responseArray)) {
            $shipoxCity = $shipoxApiClient->isValidCity($this->getStateRegion($shippingAddress));

            if ($shipoxCity['status'] == 'success') {
                $responseArray['lat'] = $shipoxCity['data']['latitude'];
                $responseArray['lon'] = $shipoxCity['data']['longitude'];
            } else {
                $geoLatLon = $shipoxGeoClient->getLatLon(null, $city, $region);

                if ($geoLatLon) {
                    $responseArray['lat'] = $geoLatLon['lat'];
                    $responseArray['lon'] = $geoLatLon['lng'];
                }
            }
        }

        return $responseArray;
    }

    /**
     * @return bool
     */
    public function isUrgentEnabled()
    {
        if (strpos(Mage::getStoreConfig('carriers/shipox/carrier_options'), 'bullet') === false)
            return false;

        return true;
    }

    /**
     * @param $shippingMethod
     * @return int|null
     */
    public function getPackageIdFromString($shippingMethod)
    {
        $items = explode("-", $shippingMethod);
        if (is_array($items))
            return intval($items[0]);

        return null;
    }

    /**
     * @param $shippingAddress
     * @return mixed
     */
    public function getStateRegion($shippingAddress)
    {
        $stateRegion = $shippingAddress->getCity();
        if (!is_null($shippingAddress->getRegion())) {
            $stateRegion = $shippingAddress->getRegion();
        }

        return $stateRegion;
    }

    /**
     * @param $menuId
     * @param $customerLatLonAddress
     * @param null $additionalData
     * @param bool $isFirstItem
     * @return null
     */
    public function getPackagesPricesList($menuId, $customerLatLonAddress, $additionalData = null, $isFirstItem = false)
    {
        $shipoxApiClient = new Shipox_Delivery_Helper_Client();
        $merchantLatLonAddress = explode(",", Mage::getStoreConfig('shipox_delivery/merchant/lat_lon'));

        if (!empty($merchantLatLonAddress)) {
            $request = array(
                "service" => Mage::getStoreConfig('api/config/service'),
                "from_lat" => $merchantLatLonAddress[0],
                "to_lat" => $customerLatLonAddress['lat'],
                "from_lon" => $merchantLatLonAddress[1],
                "to_lon" => $customerLatLonAddress['lon'],
                "menu_id" => $menuId,
                "courier_type" => (is_array($additionalData) && array_key_exists('courier_type', $additionalData)) ? $additionalData['courier_type'] : Mage::getStoreConfig('carriers/' . $this->_code . '/carrier_options')
            );

            if (is_array($additionalData) && array_key_exists('vehicle_type', $additionalData))
                $request['vehicle_type'] = $additionalData['vehicle_type'];

            $response = $shipoxApiClient->getPackagesPrices($request);

            if ($response['list']) {
                if ($isFirstItem) {
                    foreach ($response['list'] as $item) {
                        return $item['packages'][0]['id'];
                    }
                    return null;
                }
                return $response['list'];
            }
        }
        return null;
    }

    /**
     * @param $shippingAddress
     * @return string
     */
    public function getFullDestination($shippingAddress)
    {
        $response = $shippingAddress->getCountry() . " "
            . $this->getStateRegion($shippingAddress);


        $address = $shippingAddress->getStreet();

        foreach ($address as $addressItem) {
            $response .= " " . $addressItem;
        }

        return $response;
    }

    /**
     * @param $shippingMethod
     * @param $modelType
     * @return null
     */
    public function getModelItemIfExists($shippingMethod, $modelType)
    {

        $model = null;
        switch ($modelType) {
            case 'courier':
                $model = new Shipox_Delivery_Model_Couriertype();
                break;
            case 'vehicle':
                $model = new Shipox_Delivery_Model_Vehicletype();
                break;
        }

        if ($model) {
            $items = $model->toValueArray();

            foreach ($items as $item) {
                if (strpos($shippingMethod, $item) !== false) {
                    return $item;
                    break;
                }
            }
        }
        return null;
    }

    /**
     * @param $destination
     * @return null
     */
    public function extractLatLonArrayFromString($destination)
    {
        $response = null;
        $array = explode(",", $destination);

        if (count($array) == 2) {
            $response['lat'] = $array[0];
            $response['lon'] = $array[1];
        }

        return $response;
    }

    /**
     * @param $order
     * @param $packageId
     * @param $customerAddressLatLon
     * @param $shipoxOrderDetails
     * @return array
     */
    public function pushShipoxOrder($order, $packageId, $customerAddressLatLon, $shipoxOrderDetails)
    {
        $shipoxApiClient = new Shipox_Delivery_Helper_Client();

        $responseData = array();
        $requestData = array();

        $shippingAddress = $order->getShippingAddress();
        $paymentType = $order->getPayment()->getMethodInstance()->getCode();
        $merchantLatLonAddress = explode(",", Mage::getStoreConfig('shipox_delivery/merchant/lat_lon'));

        // Magento Order ID As a Reference
        $requestData['reference_id'] = $order->getIncrementId();

        //Charge Items COD
        $requestData['charge_items'] = array();

        switch ($paymentType) {
            case 'cashondelivery':
            case 'phoenix_cashondelivery':

                //Payer
                $requestData['payer'] = 'recipient';
                $requestData['parcel_value'] = $order->getBaseGrandTotal();

                $requestData['charge_items'] = array(
                    array(
                        'charge_type' => "cod",
                        'charge' => ($order->getBaseGrandTotal() - $order->getBaseShippingAmount())
                    ),
                    array(
                        'charge_type' => "service_custom",
                        'charge' => $this->getCustomService(Mage::getStoreConfig('carriers/shipox/allowed_payment_type'), $order->getBaseShippingAmount())
                    )
                );
                break;
            default:
                $requestData['payer'] = 'sender';

                $requestData['charge_items'] = array(
                    array(
                        'charge_type' => "cod",
                        'charge' => 0
                    ),
                    array(
                        'charge_type' => "service_custom",
                        'charge' => $this->getCustomService(Mage::getStoreConfig('carriers/shipox/allowed_payment_type'), $order->getBaseShippingAmount())
                    )
                );
                break;
        }

        //  PickUp Time
        $requestData['pickup_time_now'] = true;

        //  PhotoItems
        $requestData['photo_items'] = array();

        //  PackageInfo
        $requestData['package'] = array('id' => $packageId);

        //  Locations
        $requestData['locations'][] = array(
            'pickup' => true,
            'lat' => $merchantLatLonAddress[0],
            'lon' => $merchantLatLonAddress[1],
            'address' => Mage::getStoreConfig('shipox_delivery/merchant/city') . "," . Mage::getStoreConfig('shipox_delivery/merchant/address1') . "," . Mage::getStoreConfig('shipox_delivery/merchant/address2'),
            'details' => Mage::getStoreConfig('shipox_delivery/merchant/details'),
            'phone' => Mage::getStoreConfig('shipox_delivery/merchant/phone_number'),
            'contact_name' => Mage::getStoreConfig('shipox_delivery/merchant/fullname'),
            'address_city' => Mage::getStoreConfig('shipox_delivery/merchant/city'),
            'address_street' => Mage::getStoreConfig('shipox_delivery/merchant/lat_lon')
        );

        $requestData['locations'][] = array(
            'pickup' => false,
            'lat' => $customerAddressLatLon['lat'],
            'lon' => $customerAddressLatLon['lon'],
            'address' => $shipoxOrderDetails['destination'] . " " . $shippingAddress->getAddress(),
            'details' => $shippingAddress->getEmail(),
            'phone' => $shippingAddress->getTelephone(),
            'address_city' => $shippingAddress->getCity(),
            'address_street' => $shippingAddress->getStreetFull(),
            'contact_name' => $shippingAddress->getFirstname() . ' ' . $shippingAddress->getMiddlename() . ' ' . $shippingAddress->getLastname(),
        );

        //Note
        $requestData['note'] = $shipoxOrderDetails['package_note'];

        //Payment Type
        $requestData['payment_type'] = Mage::getStoreConfig('carriers/shipox/allowed_payment_type');

        // Order Reference ID
        $requestData['reference_id'] = $order->getIncrementId();

        //If Recipient Not Available
        $requestData['recipient_not_available'] = 'do_not_deliver';

        $response = $shipoxApiClient->postCreateOrder($requestData);

        if ($response['status'] == 'success') {
            $responseData = $response['data'];
        }

        return $responseData;
    }

    /**
     * @param $paymentOption
     * @param $price
     * @return int
     */
    public function getCustomService($paymentOption, $price)
    {
        if ($paymentOption == 'credit_balance')
            return 0;

        return $price;
    }

    /**
     * @param $order
     * @return array
     */
    public function generateProductQuantityArray($order)
    {
        $itemsQuantity = array();

        $items = $order->getAllItems();

        foreach ($items as $item) {
            if (!$item->getQtyToShip()) {
                continue;
            }

            if ($item->getIsVirtual()) {
                continue;
            }

            $itemId = $item->getId();
            $itemsQuantity[$itemId] = $item->getQtyOrdered();
        }

        return $itemsQuantity;
    }

    /**
     * @param $order
     * @return array
     */
    public function getProperPackagesForOrder($order)
    {
        $responseArray = array(
            'status' => false,
            'message' => '',
            'data' => null
        );

        if (!$this->isAllowedSystemCurrency()) {
            $responseArray['message'] = "Base Currency is not proper for Wing";
        }

        $shippingAddress = $order->getShippingAddress();
        $countryId = $this->getCountryWingId($shippingAddress->getCountry());

        if (!$this->isInternationalAvailable($countryId)) {
            $responseArray['message'] = "International Delivery is not available";
        }

        if ($this->isAllowedCountry($countryId)) {

            $packageWeight = $this->getPackageWeightForWing($order->getWeight());
            $menuId = $this->getPackageType($packageWeight, $countryId);
            $customerLatLonAddress = $this->getAddressLatLon($shippingAddress);

            if (!empty($customerLatLonAddress)) {
                $packagePrices = $this->getPackagesPricesList($menuId, $customerLatLonAddress);

                if ($packagePrices) {

                    $priceArr = array();
                    foreach ($packagePrices as $listItem) {
                        $packages = $listItem['packages'];
                        $name = $listItem['name'];
                        $vehicle_type = $listItem['vehicle_type'];

                        foreach ($packages as $packageItem) {
                            $label = $packageItem['delivery_label'];
                            $price = $packageItem['price']['total'];
                            $method = base64_encode($packageItem['id'] . "-" . $vehicle_type . "_" . $packageItem['courier_type']);

                            $response['type'] = 'success';
                            $priceArr[$method] = array('label' => $name . " - " . $label, 'amount' => $price);
                        }
                    }
                    $responseArray['status'] = true;
                    $responseArray['menuId'] = $menuId;
                    $responseArray['data'] = $priceArr;

                } else {
                    $responseArray['message'] = "Wing doesn't have any proper package for this Shipment";
                }
            } else {
                $responseArray['message'] = "Oops, We couldn't get customer Latitude and Longitude address";
            }
        } else {
            $responseArray['message'] = "Select Current Country as allowed";
        }

        return $responseArray;
    }

    /**
     * @param $status
     * @return mixed
     */
    public function isOrderCancellable($status)
    {
        $statusMapping = new Shipox_Delivery_Model_Statusmapping();
        $statusList = $statusMapping->statusList();

        return $statusList[$status]['cancellable'];
    }


    /**
     * @param $shipoxOrderData
     * @return bool
     */
    public function isShipoxOrderCompleted($shipoxOrderData)
    {
        return ($shipoxOrderData['status'] === 'completed') ? true : false;
    }

    /**
     * @param $status
     * @return bool
     */
    public function isShipoxOrderCanReject($status)
    {

        switch ($status) {
            case 'completed':
            case 'returned_to_shipox':
            case 'returned_to_origin':
            case 'cancelled':
                return false;
                break;
        }

        return true;
    }

    /**
     * @param $shipoxOrderId
     * @param bool $isShipoxOrderStatusUpdateNeeded
     * @param null $order
     * @return null
     * @internal param bool $isUpdateTable
     */
    public function getShipoxOrderDetails($shipoxOrderId, $isShipoxOrderStatusUpdateNeeded = false, $order = null)
    {
        $shipoxApiClient = new Shipox_Delivery_Helper_Client();
        $shipoxDBClient = new Shipox_Delivery_Helper_Dbclient();

        $shipoxDetails = $shipoxApiClient->getOrderItem($shipoxOrderId);

        if ($shipoxDetails['status'] == 'success') {
            if ($isShipoxOrderStatusUpdateNeeded) {

                $shipoxDataTable = $shipoxDBClient->getData($order->getQuoteId(), $order->getEntityId());
                if (!is_null($shipoxDataTable->getData())) {
                    $shipoxDataTable->setShipoxOrderStatus($shipoxDetails['data']['status']);
                    $shipoxDataTable->save();
                }
            }
            return $shipoxDetails['data'];
        }

        return null;
    }

    /**
     * @param $order
     * @param $reason
     * @return array
     */
    public function cancelShipoxOrder($order, $reason)
    {
        $statusMapping = new Shipox_Delivery_Model_Statusmapping();

        $shipoxOrder = Mage::getModel("shipox_delivery/tracking")->getOrderTrackingData($order);

        $responseData = array(
            'status' => false,
            'message' => 'Oops, there is some error with the Server'
        );

        if (!empty($shipoxOrder)) {

            $shipoxDetails = $this->getShipoxOrderDetails($shipoxOrder['wing_order_number'], true, $order);

            if (!empty($shipoxDetails)) {
                $shipoxApiClient = new Shipox_Delivery_Helper_Client();

                $transfer = array(
                    'note' => $reason,
                    'reason' => $reason,
                    'status' => 'cancelled'
                );

                if ($statusMapping->isOrderStatusCancellable($shipoxDetails['status'])) {
                    $response = $shipoxApiClient->updateStatus($shipoxOrder['wing_order_id'], $transfer);

                    if ($response['success'] == 'success') {
                        $responseData['status'] = true;
                        $responseData['message'] = 'Order has been cancelled.';
                    } else {
                        $responseData['message'] = $response['message'];
                    }
                }
            } else {
                $responseData['message'] = 'Cannot find order details from Wing';
            }
        }

        return $responseData;
    }

    /**
     * @param $shipoxOrder
     * @return string
     */
    public function getOrderTrackerURL($shipoxOrder)
    {
        return $shipoxOrder['shipox_order_url'] = $this->getWingSiteURL() . Mage::getStoreConfig('tracking/config/path') . $shipoxOrder['wing_order_number'];
    }


    /**
     * @param $orderId
     * @return null
     */
    public function getShipoxOrderAirWayBill($orderId)
    {
        $shipoxApiClient = new Shipox_Delivery_Helper_Client();

        $response = $shipoxApiClient->getAirwayBill($orderId);
        if ($response['status'] == 'success') {
            return $response['data']['value'];
        }

        return null;
    }


    /**
     * @param $status
     * @return bool
     */
    public function isLastOrderStatus($status)
    {

        switch ($status) {
            case 'returned_to_shipox':
            case 'returned_to_origin':
            case 'cancelled':
                return true;
                break;
        }

        return false;
    }


    /**
     * @param $isPossibleToCreateNewOrder
     * @param $shipoxOrderFromServer
     * @return bool
     */
    public function canRecreateShipoxOrder($isPossibleToCreateNewOrder, $shipoxOrderFromServer)
    {
        if ($isPossibleToCreateNewOrder)
            return true;

        if (!$isPossibleToCreateNewOrder && !Mage::getStoreConfig('carriers/shipox/reordering'))
            return false;

        if (!is_null($shipoxOrderFromServer)) {
            if ($this->isLastOrderStatus($shipoxOrderFromServer['status']))
                return true;
        }

        return false;
    }


    /**
     * @param $orderId
     */
    public function deactivateLastShipoxOrders($orderId)
    {
        $model = Mage::getModel('shipox_delivery/shipox');

        $collection = $model->getCollection()
            ->addAttributeToSelect(array('order_id', 'active_order'))
            ->addFieldToFilter('order_id', $orderId);

        Mage::getSingleton('core/resource_iterator')->walk($collection->getSelect(), array(array($this, 'orderCallback')));
    }

    /**
     * @param $args
     */
    public function orderCallback($args)
    {
        $shipoxOrder = Mage::getModel('shipox_delivery/shipox');
        $shipoxOrder->setData($args['row']);
        $shipoxOrder->setActiveOrder(0);
        $shipoxOrder->getResource()->saveAttribute($shipoxOrder, 'active_order'); // save only changed attribute instead of whole object
    }
}