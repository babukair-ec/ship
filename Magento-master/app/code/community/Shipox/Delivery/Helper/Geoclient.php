<?php

/**
 * @category   Shipox -  Google Geo Location Client
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/

class Shipox_Delivery_Helper_Geoclient extends Mage_Core_Helper_Data
{
    protected $_logFile = 'shipox_geo.log';

    /**
     * @return string
     */
    public function getUrl()
    {
        return Mage::getStoreConfig('api/geo/url');
    }


    /**
     * @return mixed
     */
    public function getRegion()
    {
        return Mage::getStoreConfig('api/geo/region');
    }


    /**
     * @return mixed
     */
    public function getGoogleApiKey() {
        return Mage::getStoreConfig('shipox_delivery/service/gmap_key');
    }


    /**
     * @param $searchString
     * @param $city
     * @param $region
     * @return null
     */
    public function getLatLon($searchString, $city, $region) {
        $stringArray = explode(" ", $searchString);

        $containedString = '';
        $city = trim($city);
        $region = trim($region);

        foreach ($stringArray as $string) {
            $string = trim($string);

            if(!empty($string))
                $containedString .= $string."+";
        }

        $containedString .= $city;

        if(!empty($region)) {
            $containedString .= "+".$region;
        }

        $data = array(
            'address' => $containedString
        );

        $response = $this->sendRequest($data);

        if($region)
            return $response['geometry']['location'];

        return null;
    }

    /**
     * @param null $data
     * @return null
     * @internal param $url
     * @internal param $search
     */
    private function sendRequest($data = null)
    {
        $apiURL = $this->getUrl();

        $data['region'] = $this->getRegion();
        $data['key'] = $this->getGoogleApiKey();
        $data['language'] = 'en';

        $client = new Zend_Http_Client($apiURL . http_build_query($data));

        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders('Content-type', 'application/json');
        $client->setHeaders('Accept', 'application/json');

        try {
            $response = $client->request();

            if ($response->isSuccessful()) {
                $responseBody = Mage::helper('core')->jsonDecode($response->getBody());

                if($responseBody['status'] == "OK") {
                    return $responseBody['results'][0];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }
}