<?php

/**
 * @category   Shipox -  DB Client
 * @package    Shipox_Delivery
 * @author     Shipox Delivery -  Umid Akhmedjanov / Furkat Djamolov
 * @website    www.shipox.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 **/

class Shipox_Delivery_Helper_Dbclient extends Mage_Core_Helper_Data
{
    protected $_logFile = 'shipox_db.log';

    /**
     * @param $data
     * @return mixed|null
     */
    public function insertData($data)
    {

        $model = Mage::getModel('shipox_delivery/shipox');
        $collection = $model->getCollection()->addFieldToFilter('quote_id', $data['quote_id'])->getFirstItem();

        if (!$collection->getId()) {

            $model = Mage::getModel('shipox_delivery/shipox')->setData($data);
            try {
                $model->save();

                $insertId = $model->getId();

                return $insertId;
            } catch (Exception $e) {
                Mage::log('DB Insert Error: ' . $e->getMessage(), null, $this->_logFile);
            }
        } else {
            $itemId = $collection->getId();
            return $this->updateData($itemId, $data);
        }

        return false;
    }


    /**
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateData($id, $data)
    {
        $model = Mage::getModel('shipox_delivery/shipox');

        $item = $model->load($id)->addData($data);
        try {
            $item->setId($id)->save();
            return true;

        } catch (Exception $e){
            Mage::log('DB Update Error: ' . $e->getMessage(), null, $this->_logFile);
        }

        return false;
    }


    /**
     * @param $quoteId
     * @param null $orderId
     * @return mixed|null
     * @internal param $id
     */
    public function getData($quoteId, $orderId = null)
    {
        $model = Mage::getModel('shipox_delivery/shipox');


        if ($orderId) {
            $collection = $model->getCollection()->addFieldToFilter('order_id', $orderId)->getFirstItem();
        } else {
            $collection = $model->getCollection()->addFieldToFilter('quote_id', $quoteId)->getFirstItem();
        }

        if ($collection->getId()) {
            return $model->load($collection->getId());
        }

        return null;
    }
}