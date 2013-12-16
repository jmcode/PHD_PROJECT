<?php
/**
 * Shopping Assistant
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitshopassist
 * @version      1.0.17
 * @license:     fEv8NRVUcfeWNj6fFopfiC6j0bkfeMCgY3lx8CzFS6
 * @copyright:   Copyright (c) 2013 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2012 AITOC, Inc.
 */

class Aitoc_Aitshopassist_Model_Mysql4_Rewrite_CatalogResourceLayerFilterPrice extends Mage_Catalog_Model_Resource_Layer_Filter_Price
{
    protected function _getSelect($filter)
    {
	    if(version_compare(Mage::getVersion(), '1.7.0.0', '<'))
		    return parent::_getSelect($filter);
		
		$select = parent::_getSelect($filter);
		
		$modifyingFlag = false;
		$wherePart = $select->getPart(Zend_Db_Select::WHERE);
        foreach($wherePart as $key => $wherePartItem)
		{
		    if(strpos($wherePartItem, '`' . Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS . '`.`sku`') > 0)
			{
			    $modifyingFlag = true;
			    $wherePartItem = str_replace('`' . Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS .'`.`sku`', '`aitoc_e`.`sku`', $wherePartItem);
				$wherePart[$key] = $wherePartItem;
			}
		}
		if(!$modifyingFlag)
		    return $select;
			
        $select->setPart(Zend_Db_Select::WHERE, $wherePart);
			
        $fromPart = $select->getPart(Zend_Db_Select::FROM);
		$fromPart['aitoc_e'] = array('joinType' => 'inner join',
		                             'schema' => null,
									 'tableName' => 'catalog_product_entity',
									 'joinCondition' => 'aitoc_e.entity_id = ' . Mage_Catalog_Model_Resource_Product_Collection::MAIN_TABLE_ALIAS . '.entity_id');
		$select->setPart(Zend_Db_Select::FROM, $fromPart);

        return $select;
    }
}