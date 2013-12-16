<?php
/* Matrixdays
 *
 * @category   Webshopapps
 * @package    Webshopapps_matrixdays
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Matrixdays_Model_Carrier_Source_Timeslots {

public function toOptionArray()
    {
        $matrixdays = Mage::getSingleton('matrixdays/carrier_matrixdays');
        $arr = array();
        foreach ($matrixdays->getCode('timeslots') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>Mage::helper('shipping')->__($v));
        }
        return $arr;
    }
}
