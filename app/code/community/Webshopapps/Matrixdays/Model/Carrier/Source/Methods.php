<?php
/* ProductMatrix
 *
 * @category   Webshopapps
 * @package    Webshopapps_productmatrix
 * @copyright  Copyright (c) 2011 WebShopApps Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Matrixdays_Model_Carrier_Source_Methods {

    public function toOptionArray()
    {
        $productmatrix = Mage::getSingleton('matrixdays/carrier_matrixdays');
    	$arr = array();
        foreach ($productmatrix->getAllowedMethods() as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }
}
