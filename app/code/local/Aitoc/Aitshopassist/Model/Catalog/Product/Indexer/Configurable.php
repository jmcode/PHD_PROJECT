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
class Aitoc_Aitshopassist_Model_Catalog_Product_Indexer_Configurable extends Mage_Catalog_Model_Product_Indexer_Eav
{
    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('aitshopassist')->__('Configurable Product Attributes');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('aitshopassist')->__('Index configurable product attributes for Aitoc Shop Assistant filtering');
    }

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('aitshopassist/catalog_product_indexer_configurable');
    }
}