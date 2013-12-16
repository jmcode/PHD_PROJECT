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

class Aitoc_Aitshopassist_Model_Rewrite_CatalogProductAction extends Mage_Catalog_Model_Product_Action
{
    public function updateAttributes($productIds, $attrData, $storeId)
    {
        $result = parent::updateAttributes($productIds, $attrData, $storeId);

        Mage::dispatchEvent('catalog_product_attribute_update_after');

        return $result;
    }
}