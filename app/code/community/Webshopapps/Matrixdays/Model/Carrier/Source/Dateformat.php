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
 
class Webshopapps_Matrixdays_Model_Carrier_Source_Dateformat  {
	
    public function toOptionArray()  {
    	
        $matrixdays = Mage::getSingleton('matrixdays/carrier_matrixdays');
        $arr = array();
        foreach ($matrixdays->getCode('date_format') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }
    
    public function getDestString($code) {
        $matrixdays = Mage::getSingleton('matrixdays/carrier_matrixdays');
    	return $matrixdays->getCode('date_format',$code);
    }
    
    
}
?>