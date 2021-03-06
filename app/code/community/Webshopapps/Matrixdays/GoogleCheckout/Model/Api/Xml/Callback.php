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
 * @package    Mage_GoogleCheckout
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Webshopapps_Matrixdays_GoogleCheckout_Model_Api_Xml_Callback extends Mage_GoogleCheckout_Model_Api_Xml_Callback
{
	
	    protected function _responseMerchantCalculationCallback()
    {
        $merchantCalculations = new GoogleMerchantCalculations($this->getCurrency());

        $quoteId = $this->getData('root/shopping-cart/merchant-private-data/quote-id/VALUE');
        $storeId = $this->getData('root/shopping-cart/merchant-private-data/store-id/VALUE');
        $quote = Mage::getModel('sales/quote')
            ->setStoreId($storeId)
            ->load($quoteId);

        $billingAddress = $quote->getBillingAddress();
        $address = $quote->getShippingAddress();

        $googleAddress = $this->getData('root/calculate/addresses/anonymous-address');

        $googleAddresses = array();
        if ( isset( $googleAddress['id'] ) ) {
            $googleAddresses[] = $googleAddress;
        } else {
            $googleAddresses = $googleAddress;
        }

        $methods = Mage::getStoreConfig('google/checkout_shipping_merchant/allowed_methods', $this->getStoreId());
        $methods = unserialize($methods);
        $limitCarrier = array();
        foreach ($methods['method'] as $method) {
            if ($method) {
                list($carrierCode, $methodCode) = explode('/', $method);
                $limitCarrier[] = $carrierCode;
            }
        }

        foreach($googleAddresses as $googleAddress) {
            $addressId = $googleAddress['id'];


            $regionCode = $googleAddress['region']['VALUE'];
            $countryCode = $googleAddress['country-code']['VALUE'];
            $regionModel = Mage::getModel('directory/region')->loadByCode($regionCode, $countryCode);
            $regionId = $regionModel->getId();

            $address->setCountryId($countryCode)
                ->setRegion($regionCode)
                ->setRegionId($regionId)
                ->setCity($googleAddress['city']['VALUE'])
                ->setPostcode($googleAddress['postal-code']['VALUE'])
                ->setLimitCarrier($limitCarrier);
            $billingAddress->setCountryId($countryCode)
                ->setRegion($regionCode)
                ->setRegionId($regionId)
                ->setCity($googleAddress['city']['VALUE'])
                ->setPostcode($googleAddress['postal-code']['VALUE'])
                ->setLimitCarrier($limitCarrier);

            $address->setCollectShippingRates(true)
                ->collectShippingRates()
                ->collectTotals();
            $billingAddress->collectTotals();

            if ($gRequestMethods = $this->getData('root/calculate/shipping/method')) {
                $carriers = array();
                $errors = array();
                foreach (Mage::getStoreConfig('carriers', $this->getStoreId()) as $carrierCode=>$carrierConfig) {
                    if (!isset($carrierConfig['title'])) {
                        continue;
                    }
                    $title = $carrierConfig['title'];
                    foreach ($gRequestMethods as $method) {
                        $methodName = is_array($method) ? $method['name'] : $method;
                        if ($title && $method && strpos($methodName, $title)===0) {
                            $carriers[$carrierCode] = $title;
                            $errors[$title] = true;
                        }
                    }
                }

                $result = Mage::getModel('shipping/shipping')
                    ->collectRatesByAddress($address, array_keys($carriers))
                    ->getResult();

                $rates = array();
                $rateCodes = array();
                foreach ($result->getAllRates() as $rate) {
                    if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error) {
                        $errors[$rate->getCarrierTitle()] = 1;
                    } else {
                        $k = $rate->getCarrierTitle().' - '.$rate->getMethodTitle();

                        if ($address->getFreeShipping()) {
                            $price = 0;
                        } else {
                            $price = $rate->getPrice();
                        }

                        if ($price) {
                            $price = Mage::helper('tax')->getShippingPrice($price, false, $address);
                        }

                        $rates[$k] = $price;
                        $rateCodes[$k] = $rate->getCarrier() . '_' . $rate->getMethod();
                        unset($errors[$rate->getCarrierTitle()]);
                    }
                }

                foreach ($gRequestMethods as $method) {
                    $methodName = is_array($method) ? $method['name'] : $method;
                    $result = new GoogleResult($addressId);

                    if (!empty($errors)) {
                        $continue = false;
                        foreach ($errors as $carrier=>$dummy) {
                            if (strpos($methodName, $carrier)===0) {
                                $result->SetShippingDetails($methodName, 0, "false");
                                $merchantCalculations->AddResult($result);
                                $continue = true;
                                break;
                            }
                        }
                        if ($continue) {
                            continue;
                        }
                    }

                    if (isset($rates[$methodName])) {
                        if ($this->getData('root/calculate/tax/VALUE')=='true') {
                            $address->setShippingMethod($rateCodes[$methodName]);

                            $taxAmount = $address->getBaseTaxAmount();
                            $taxAmount += $billingAddress->getBaseTaxAmount();

                            $result->setTaxDetails($taxAmount);
                        }

                        $result->SetShippingDetails($methodName, $rates[$methodName], "true");
                        $merchantCalculations->AddResult($result);
                    } else if (!empty($method) && !$method=="")  { //KB EDIT

                    	$result->SetShippingDetails($methodName, 0, "false");
        				$merchantCalculations->AddResult($result);
                    	
                    }
                }
            } elseif ($this->getData('root/calculate/tax/VALUE')=='true') {
                $address->setShippingMethod(null);

                $address->setCollectShippingRates(true)->collectTotals();
                $billingAddress->setCollectShippingRates(true)->collectTotals();

                $taxAmount = $address->getBaseTaxAmount();
                $taxAmount += $billingAddress->getBaseTaxAmount();

                $result = new GoogleResult($addressId);
                $result->setTaxDetails($taxAmount);
                $merchantCalculations->addResult($result);
            }
        }

        $this->getGResponse()->ProcessMerchantCalculations($merchantCalculations);
    }
	
}