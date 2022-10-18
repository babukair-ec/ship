<?php

/**
 * Created by PhpStorm.
 * User: Umid Akhmedjanov / Furkat Djamolov
 * Modified: Furkat Djamolov
 * Date: 02.09.2019
 * Time: 17:29
 */
class Shipox_Delivery_Helper_Client extends Mage_Core_Helper_Data
{
    protected $_logFile = 'shipox_api.log';
    private $_timeout = 60 * 60 * 23;

    /**
     * @param $data
     * @return null
     */
    public function authenticate($data)
    {
        $data['remember_me'] = true;

        $response = $this->sendRequest(Mage::getStoreConfig('api/destination/authenticate'), 'post', $data, true);
        return $response;
    }

    /**
     * Check Token is expired or not, if expired reauthorize Wing and refresh Token
     * @return bool
     */
    public function checkTokenExpired()
    {
        if (Mage::getModel('core/date')->timestamp() - Mage::getStoreConfig('shipox_delivery/auth/token_time') > $this->_timeout) {
            if (is_null(Mage::getStoreConfig('shipox_delivery/auth/user_name')) && is_null(Mage::getStoreConfig('shipox_delivery/auth/password'))) {
                Mage::log('Time: ' . print_r("Check Token Expired Function: Merchant option is empty", true), null, $this->_logFile);
                return false;
            }

            $requestData = array(
                'username' => Mage::getStoreConfig('shipox_delivery/auth/user_name'),
                'password' => Mage::getStoreConfig('shipox_delivery/auth/password'),
            );

            $response = $this->authenticate($requestData);

            if ($response['status'] == 'success') {
                $data = $response['data'];
                Mage::getModel('core/config')->saveConfig('shipox_delivery/auth/jwt_token', $data['id_token']);
                Mage::getModel('core/config')->saveConfig('shipox_delivery/auth/token_time', Mage::getModel('core/date')->timestamp());

                return $data['id_token'];
            }

            Mage::log('Login Error: ' . print_r($response['data'], true), null, $this->_logFile);
            return false;

        }
        return Mage::getStoreConfig('shipox_delivery/auth/jwt_token');
    }

    /**
     * @param $url
     * @param string $requestMethod
     * @param null $data
     * @param bool $getToken
     * @return null
     */
    private function sendRequest($url, $requestMethod = 'get', $data = null, $getToken = false)
    {
        $apiURL = $this->getAPIBaseURl();

        $token = Mage::getStoreConfig('shipox_delivery/auth/jwt_token');

        if (!$getToken) {
            $token = $this->checkTokenExpired();
            if (!$token) {
                $response['data']['code'] = 'error.validation';
                $response['data']['message'] = 'Token Expired and cannot re-login to the System';
                return $response;
            }
        }

        $client = new Zend_Http_Client($apiURL . $url);

        switch ($requestMethod) {
            case 'get':
                $client->setMethod(Zend_Http_Client::GET);
                break;
            case 'post':
                $client->setMethod(Zend_Http_Client::POST);
                break;
            case 'put':
                $client->setMethod(Zend_Http_Client::PUT);
                break;
            case 'delete':
                $client->setMethod(Zend_Http_Client::DELETE);
                break;
        }

        $json = $data ? json_encode($data) : '';

        $client->setConfig(array('timeout' => 30));
        $client->setRawData($json, 'application/json');
        $client->setHeaders('Content-type', 'application/json');
        $client->setHeaders('x-app-type', 'magento-v1-api');

        if (!$getToken) {
            if ($token) {
                $client->setHeaders('Authorization', 'Bearer ' . $token);
                $client->setHeaders('Accept', 'application/json');
            } else {
                $client->setHeaders('Accept', '*/*');
            }
        } else {
            $client->setHeaders('Accept', '*/*');
        }

        try {
            $response = $client->request();

            if ($response->isSuccessful()) {
                return Mage::helper('core')->jsonDecode($response->getBody());
            } else {
                return Mage::helper('core')->jsonDecode($response->getBody());
            }
        } catch (Exception $e) {
            Mage::log('Exception: ' . print_r($e->getMessage(), true), null, $this->_logFile);
            return null;
        }
    }

    /**
     * @return string
     */
    public function getAPIBaseURl()
    {
        if (Mage::getStoreConfig('shipox_delivery/service/sandbox_flag') == 1) {
            return Mage::getStoreConfig('api/config/staging');
        }
        return Mage::getStoreConfig('api/config/live');
    }

    /**
     * @return null
     */
    public function getCountryList()
    {
        $response = $this->sendRequest(Mage::getStoreConfig('api/destination/country_list'));
        return $response ? $response['data'] : $response;
    }

    /**
     * @param bool $isDomestic
     * @return null
     */
    public function getCityList($isDomestic = false)
    {
        $data = array(
            'is_uae' => $isDomestic
        );

        $response = $this->sendRequest(Mage::getStoreConfig('api/destination/city_list'). "?" . http_build_query($data), 'get');

        return $response ? $response['data'] : $response;
    }

    /**
     * @param $cityId
     * @return null
     */
    public function getCity($cityId) {
        $response = $this->sendRequest(str_replace("{id}", $cityId, Mage::getStoreConfig('api/destination/city')));
        return $response;
    }

    /**
     * @param string $query
     * @return null
     */
    public function getPackageMenu($query = '')
    {
        $response = $this->sendRequest(Mage::getStoreConfig('api/destination/package_menu') . $query);
        return $response ? $response['data'] : $response;
    }

    /**
     * @param $city
     * @return null
     */
    public function isValidCity($city)
    {
        $data = array(
            'city_name' => $city
        );

        $response = $this->sendRequest(Mage::getStoreConfig('api/destination/city_by_name') . "?" . http_build_query($data), 'get');
        return $response;
    }

    /**
     * @param $data
     * @return null
     * @internal param $query
     */
    public function getPackagesPrices($data)
    {

        if ($data) {
            $response = $this->sendRequest(Mage::getStoreConfig('api/destination/packages_prices') . "?" . http_build_query($data));
            return $response ? $response['data'] : $response;
        }

        return null;
    }

    /**
     * @param $data
     * @return null
     */
    public function postCreateOrder($data)
    {
        $response = $this->sendRequest(Mage::getStoreConfig('api/destination/order'), 'post', $data);
        return $response;
    }

    /**
     * @param string $orderNumber
     * @return null
     */
    public function getOrderItem($orderNumber = '')
    {
        $response = $this->sendRequest(Mage::getStoreConfig('api/destination/order_item') . "/" . $orderNumber);
        return $response;
    }

    /**
     * @param $orderId
     * @param $data
     * @return null
     */
    public function updateStatus($orderId, $data)
    {
        $response = $this->sendRequest(str_replace("{id}", $orderId, Mage::getStoreConfig('api/destination/status_update')), 'put', $data);
        return $response;
    }


    /**
     * @param $orderId
     * @return null
     */
    public function getAirwayBill($orderId)
    {
        $response = $this->sendRequest(str_replace("{id}", $orderId, Mage::getStoreConfig('api/destination/get_airwaybill')));
        return $response;
    }
}