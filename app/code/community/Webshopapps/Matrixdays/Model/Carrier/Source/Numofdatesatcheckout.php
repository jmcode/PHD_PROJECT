<?php
/* Matrixdays
 *
 * @category   Webshopapps
 * @package    Webshopapps_matrixdays
 * @copyright  Copyright (c) 2012 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */
 
class Webshopapps_Matrixdays_Model_Carrier_Source_Numofdatesatcheckout  {
	
    public function toOptionArray()  {
    	
        $matrixdays = Mage::getSingleton('matrixdays_shipping/carrier_matrixdays');
        $arr = array();
        foreach ($matrixdays->getCode('num_of_dates_at_checkout') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }
}
?>