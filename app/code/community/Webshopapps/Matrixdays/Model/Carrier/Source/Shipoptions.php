<?php
/* Matrixdays
 *
 * @category   Webshopapps
 * @package    Webshopapps_matrixdays
 * @copyright  Copyright (c) 2010 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Matrixdays_Model_Carrier_Source_Shipoptions {

public function toOptionArray()
    {
        $matrixdays = Mage::getSingleton('matrixdays/carrier_matrixdays')->getCode('shipoptions');

        if(Mage::getConfig()->getNode('matrixdays/specialvars/volume') != 1) {
            unset($matrixdays['filter_volume']);
        }

        $arr = array();
        foreach ($matrixdays as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>Mage::helper('shipping')->__($v));
        }

        return $arr;
    }
}
