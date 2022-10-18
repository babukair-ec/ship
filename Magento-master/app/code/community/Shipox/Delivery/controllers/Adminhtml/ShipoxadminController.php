<?php

/**
 * @category   Shipox -  Admin Controller
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/
class Shipox_Delivery_Adminhtml_ShipoxadminController extends Mage_Adminhtml_Controller_Action
{
    protected $_logFile = 'shipox_api.log';

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     *   Get Token Action
     */
    public function getTokenAction()
    {
        $params = $this->getRequest()->getPost();

        $shipoxApiClient = Mage::helper('shipox_delivery/client');

        $response = Mage::app()->getResponse()
            ->setHeader('content-type', 'application/json; charset=utf-8');

        $result = array(
            'status' => 0,
            'description' => $this->__('An error has occurred. Please, contact the store administrator.')
        );

        $requestData = array(
            'username' => $params['username'],
            'password' => $params['password'],
        );

        $responsedData = $shipoxApiClient->authenticate($requestData);

        if ($responsedData['status'] == 'success') {
            $data = $responsedData['data'];
            Mage::getModel('core/config')->saveConfig('shipox_delivery/auth/jwt_token', $data['id_token']);
            Mage::getModel('core/config')->saveConfig('shipox_delivery/auth/token_time', Mage::getModel('core/date')->timestamp());

            $result['status'] = 1;
            $result['description'] = '';
        }

        $response->setBody(json_encode($result));

    }

    /**
     *  Wing Push Order from Sales Order View
     */
    public function createorderAction()
    {
        $params = $this->getRequest()->getPost();

        $response = Mage::app()->getResponse()
            ->setHeader('content-type', 'application/json; charset=utf-8');

        $shipoxDBClient = new Shipox_Delivery_Helper_Dbclient();
        $ShipoxCarrier = new Shipox_Delivery_Model_Carrier();

        $responseData = array(
            'status' => false,
            'message' => 'Oops, there is some error with the Server'
        );

        if ($params['menuId'] > 0 && $params['packageName'] && $params['orderId'] > 0) {
            $order = Mage::getModel("sales/order")->load($params['orderId']);
            $orderData = $order->getData();

            if (!empty($orderData)) {
                $shipoxHelper = new Shipox_Delivery_Helper_Data();

                $shippingAddress = $order->getShippingAddress();
                $shippingMethod = base64_decode($params['packageName']);
                $packageId = $shipoxHelper->getPackageIdFromString($shippingMethod);
                $customerLatLonAddress = $shipoxHelper->getAddressLatLon($shippingAddress);

                if ($packageId && !empty($customerLatLonAddress)) {

                    $data = array(
                        'quote_id' => $order->getQuoteId(),
                        'wing_menu_id' => $params['menuId'],
                        'destination' => $shipoxHelper->getFullDestination($shippingAddress),
                        'destination_latlon' => $customerLatLonAddress['lat'] . "," . $customerLatLonAddress['lon'],
                        'wing_package_id' => $packageId,
                        'order_id' => $order->getId(),
                        'package_note' => $params['packageNote'],
                    );

                    $responseOrder = $shipoxHelper->pushShipoxOrder($order, $packageId, $customerLatLonAddress, $data);

                    if(!empty($responseOrder)) {
                        $data['wing_order_id'] = $responseOrder['id'];
                        $data['wing_order_number'] = $responseOrder['order_number'];
                        $data['wing_order_status'] = $responseOrder['status'];
                        $data['completed_at'] = Varien_Date::now();
                        $data['is_completed'] = 1;
                        $data['is_active_order'] = 1;

                        if ($shipoxDBClient->insertData($data)) {
                            if(Mage::getStoreConfig('carriers/shipox/is_create_shipment')) {
                                $ShipoxCarrier->setShipmentAndTrackingNumberOnShipmentV2($order, $responseOrder['order_number']);
                            }
                        }

                        $responseData['status'] = true;
                        $responseData['message'] = 'Order has been successfully created';
                    }

                }

            }
        }
        $response->setBody(json_encode($responseData));
    }


    /**
     * Cancel Wing Order from Sales Order View
     * @return array
     */
    public function cancelorderAction() {
        $params = $this->getRequest()->getPost();

        $response = Mage::app()->getResponse()
            ->setHeader('content-type', 'application/json; charset=utf-8');

        $shipoxHelper = new Shipox_Delivery_Helper_Data();
        $shipoxDBClient = new Shipox_Delivery_Helper_Dbclient();

        $responseData = array(
            'status' => false,
            'message' => 'Oops, there is some error with the Server'
        );

        if ($params['orderId'] > 0) {
            $order = Mage::getModel("sales/order")->load($params['orderId']);

            if (!is_null($order->getData())) {
                $responseData = $shipoxHelper->cancelWingOrder($order, $params['cancelReason']);
                if(!is_null($responseData) && $responseData['status']) {

                    $wingDataTable = $shipoxDBClient->getData($order->getQuoteId());
                    $wingDataTable->setIsCompleted(0);
                    $wingDataTable->save();
                }
            } else {
                $responseData['message'] = "Cannot find Order Details";
            }
        } else {
            $responseData['message'] = "Cannot find Order Details";
        }

        $response->setBody(json_encode($responseData));
    }
}