<?php
/**
 * WebShopApps.com
 *
[[[WEBSHOPAPPS_COPYRIGHT_TEXT]]]
 *
 * @category   WebShopApps
 * @package    WebShopApps_Invoicing
[[[WEBSHOPAPPS_COPYRIGHT]]]
 */
 
class Webshopapps_Matrixdays_Model_Carrier_Source_Daysofweek  {
	
    public function toOptionArray()  {
    	
        $matrixdays = Mage::getSingleton('matrixdays_shipping/carrier_matrixdays');
        $arr = array();
        foreach ($matrixdays->getCode('days') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }
}
?>