<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout status
 *
 * @category   Mage
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Webshopapps_Matrixdays_Block_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{

	private $_daysOfWeek = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
	private static $_showWholePrices;

    public function isMatrixDaysEnabled()
    {
        return Mage::getStoreConfig('carriers/matrixdays/active') && in_array('show_information',
        	explode(',',Mage::getStoreConfig("carriers/matrixdays/ship_options")));
    }

    private static function showWholePrices() {
    	if (self::$_showWholePrices==NULL) {
    		$_options = explode(',',Mage::getStoreConfig("carriers/matrixdays/ship_options"));
    		self::$_showWholePrices =  in_array('show_whole_prices',$_options);
		}
		return self::$_showWholePrices;
    }

   public function getCustomText1()
    {
    	$customText = Mage::getStoreConfig('carriers/matrixdays/custom_text_1');
    	$prodDays = Mage::getStoreConfig('carriers/matrixdays/production_days');
    	$customText = str_replace("%PROD_DAYS%",$prodDays,$customText);
    	return $customText;
    }

    public function getCodes($arg)
    {
        $ref = array();
        foreach($arg as $key=>$v)
        {
            array_push($ref,$key);
        }
        return $ref;
    }

	public function getCustomText2()
    {
        $customText = Mage::getStoreConfig('carriers/matrixdays/custom_text_2');
        $dispatchDate = $this->getAddress()->getDispatchDate();

    	$customText = str_replace("%END_DATE%",$dispatchDate,$customText);

        return $customText;
    }

    public function getBlackoutDeliveryDays() {
    	return Mage::helper('core')->jsonEncode(Mage::helper('matrixdays')->getBlackoutDeliveryDays());
    }

 	public function getBlackoutDeliveryDates() {
 		$dates = Mage::helper('matrixdays')->getBlackoutDeliveryDates();
 		$pushedDates=array();
 		Mage::helper('matrixdays')->getDateFormatString() == 'dd-mm-yy' ? $split = '-' : $split = '/';

 		foreach ($dates as $date) {
 			$splitDates[]=explode($split,$date);
 			$pushedDates[]=$splitDates;
 			unset($splitDates);
 		}

 		return Mage::helper('core')->jsonEncode($pushedDates);
    }

    public function getMinDate() {

    	$earliestDate = $this->getAddress()->getEarliest();
 		if ($earliestDate!='') {
 			return $earliestDate;
 		}
 		return date("D M j G:i:s T Y",Mage::app()->getLocale()->storeTimeStamp());
    }

    public function getGridType() {
    	return Mage::helper('matrixdays')->getGridType();
    }

    public function getShippingPrice($price, $flag) {
    	if (self::showWholePrices()) {
    		return preg_replace('(\.[0-9][0-9])','',parent::getShippingPrice($price, $flag));
    	}
    	else {
    		return parent::getShippingPrice($price, $flag);
    	}
    }

    public function getInitialRates() {
        $address = $this->getAddress();
        $checkedCode = '';
        $selectedDate = '';
        $_shippingRateGroups = $address->getGroupedAllShippingRates();

        if ($address->getShippingMethod()!='') {
        	// have pre-selected the rate, so use this
    		$selectedDate = $this->getAddress()->getExpectedDelivery();
    		$checkedCode = $address->getShippingMethod();
        } else {
    		$price = 1000000;
    		foreach ($_shippingRateGroups as $code => $rates) {
	        	if ($code != 'matrixdays') {
	        		continue;
	        	}
	        	foreach ($rates as $rate) {
	        		if ($rate->getPrice()<$price) {
	        			$selectedDate = $rate->getExpectedDelivery();
	        			$checkedCode = $rate->getCode();
	        			$price = $rate->getPrice();
	        		}
	        	}
	        	break;
    		}
        }


    	return $this->_buildResultSet($_shippingRateGroups,$checkedCode,$selectedDate);

    }


    private function _buildResultSet($_shippingRateGroups,$checkedCode,$selectedDate) {
    	$resultSet = array();
    	foreach ($_shippingRateGroups as $code => $rates) {
        	if ($code != 'matrixdays') {
        		continue;
        	}
	    	foreach ($rates as $rate) {
	        	if($selectedDate!=$rate->getExpectedDelivery()) {
	        		continue;
	        	}
		    	$resultSet[] = array(
		        	'code'					=> $rate->getCode(),
		        	'price' 				=> $this->getShippingPrice($rate->getPrice(), Mage::helper('tax')->displayShippingPriceIncludingTax()),
		        	'method_description' 	=> $rate->getMethodDescription(),
		        	'checked'				=> $rate->getCode()===$checkedCode ? true : false,
		    		'expected_delivery'		=> $selectedDate,
		        );
	    	}
	    	break;
    	}
        return $resultSet;
    }


}