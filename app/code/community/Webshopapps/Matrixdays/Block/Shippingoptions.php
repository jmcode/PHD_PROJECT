<?php

/* Matrix Days
 *
 * @category   Webshopapps
 * @package    Webshopapps_matrixdays
 * @copyright  Copyright (c) 2012 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Matrixdays_Block_Shippingoptions extends Mage_Catalog_Block_Product_Abstract
{

	public function getShippingRates()
	{
		$_product = $this->getProduct();
		return $this->_getShippingRates($_product->getAttributeText('package_id'), floatval($_product->getWeight()),0);
	}

	public function _getShippingRates($package_id = null, $weight = null, $regionFilter=0)
	{
		$collection = Mage::getResourceModel('matrixdays_shipping/carrier_matrixdays_collection');
		$collection->setCountryFilter(Mage::getConfig()->getNode('general/country/default', 'store', Mage::app()->getStore()->getCode()));
		$collection->setRegionFilter($regionFilter);
		$collection->setPackageId($package_id);
		$collection->setWeightRange($weight);
		return $collection->load();
	}

	public function getRow($item)
	{
		return $item->getData('dest_country') . ',' . $item->getData('dest_region_id') . ','. $item->getData('price') . ',' . $item->getData('delivery_type');
	}

	public function getPrice($item)
	{
		return $item->getData('price');
	}

	public function getDeliveryType($item)
	{
		return $item->getData('delivery_type');
	}
}