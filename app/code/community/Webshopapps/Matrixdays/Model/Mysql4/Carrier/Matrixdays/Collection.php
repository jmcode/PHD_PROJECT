<?php
/**
 *  Webshopapps Shipping Module
 *
 * NOTICE OF LICENSE
 *
 * This code is copyright of Zowta Ltd trading under webshopapps.com
 * As such it muse not be distributed in any form
 *
 * DISCLAIMER
 *
 * It is highly recommended to backup your server files and database before installing this module.
 * No responsibility can be taken for any adverse effects installation or advice may cause. It is also
 * recommended you install on a test server initially to carry out your own testing.
 *
 * @category   Webshopapps
 * @package    Webshopapps_Matrixdays
 * @copyright  Copyright (c) 2010 Zowta Ltd (http://www.webshopapps.com)
 * @license    www.webshopapps.com/license/license.txt
 * @author     Webshopapps <sales@webshopapps.com>
*/
class Webshopapps_Matrixdays_Model_Mysql4_Carrier_Matrixdays_Collection extends Varien_Data_Collection_Db
{
    protected $_shipTable;
    protected $_countryTable;
    protected $_regionTable;

    public function __construct()
    {
        parent::__construct(Mage::getSingleton('core/resource')->getConnection('shipping_read'));
        $this->_shipTable = Mage::getSingleton('core/resource')->getTableName('matrixdays_shipping/matrixdays');
        $this->_countryTable = Mage::getSingleton('core/resource')->getTableName('directory/country');
        $this->_regionTable = Mage::getSingleton('core/resource')->getTableName('directory/country_region');
        $this->_select->from(array("s" => $this->_shipTable))
            ->joinLeft(array("c" => $this->_countryTable), 'c.country_id = s.dest_country_id', 'iso3_code AS dest_country')
            ->joinLeft(array("r" => $this->_regionTable), 'r.region_id = s.dest_region_id', 'code AS dest_region')
            ->order(array("dest_country", "dest_region", "dest_zip"));
        $this->_setIdFieldName('pk');
        return $this;
    }

    public function setWebsiteFilter($websiteId)
    {
        $this->_select->where("website_id = ?", $websiteId);

        return $this;
    }

    public function setConditionFilter($conditionName)
    {
        $this->_select->where("condition_name = ?", $conditionName);

        return $this;
    }

    public function setCountryFilter($countryId)
    {
    	$this->_select->where("dest_country_id = ?", $countryId);
    	
        return $this;
    }
    
    public function setRegionFilter($regionId)
    {
    	$this->_select->where("dest_region_id = ?", $regionId);
    	
        return $this;
    }
    
    
    public function setLimit() {
    
    	$this->_select->limit(1);
    
    }
    
   public function setPackageId($packageId)
    {
    	$this->_select->where("package_id = ?", $packageId);
    	
        return $this;
    }
    
	public function setDistinctDeliveryTypeFilter() {
    	
    	$this->_select->reset(Zend_Db_Select::COLUMNS);
    	$this->_select->reset(Zend_Db_Select::ORDER);
    	$this->_select->distinct(true);
    	$this->_select->columns('delivery_type');
    	$this->_select->order('delivery_type');
        return $this;
    }
    
   public function setWeightRange($weight)
    {
    	$this->_select->where('weight_from_value<?', $weight); 
	$this->_select->where('weight_to_value>=?', $weight);
		    	
        return $this;
    }
    public function getSkuCosts($sku, $collection)
    {
    	$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
		$collection->setPackageId($product->getAttributeText('package_id'));
		$collection->setWeightRange(floatval($product->getWeight()));			
		return $collection->load();
		
   }
    
}