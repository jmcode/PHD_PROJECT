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


class Webshopapps_Matrixdays_Block_Checkout_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{
 public function isMatrixDaysEnabled()
    {
        return Mage::getStoreConfig('carriers/matrixdays/active') && in_array('show_information',
        	explode(',',Mage::getStoreConfig("carriers/matrixdays/ship_options")));
        
    }
    
   public function getCustomText1()
    {
    	$customText = Mage::getStoreConfig('carriers/matrixdays/custom_text_1');
    	$prodDays = Mage::getStoreConfig('carriers/matrixdays/production_days');
    	$customText = str_replace("%PROD_DAYS%",$prodDays,$customText);
    	return $customText;
    }
     
  	public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }
    
	public function getCustomText2()
    {
    	
        $customText = Mage::getStoreConfig('carriers/matrixdays/custom_text_2');
        $dispatchDate = $this->getAddress()->getDispatchDate();
    	
    	$customText = str_replace("%END_DATE%",$dispatchDate,$customText);
    	
        return $customText;
    }
    
	public function usingGrid() {
    	return Mage::helper('matrixdays')->getUsingGrid();
    }
}