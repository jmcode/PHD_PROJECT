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

class Aitoc_Aitshopassist_Model_Observer
{
    public function onCoreBlockAbstractToHtmlBefore(Varien_Event_Observer $observer)
    {
        Mage::unregister('ait_current_block');
        Mage::register('ait_current_block',$observer->getEvent()->getBlock()->getNameInLayout());
    }

    public function onCatalogProductCollectionLoadBefore(Varien_Event_Observer $observer)
    {
        if(Mage::helper('aitshopassist')->allowFilter(Mage::registry('ait_current_block')))
        {
            Mage::helper('aitshopassist')->applyAitanswerFilters($observer->getEvent()->getCollection());
        }
    }

    public function onCatalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
        {
            /**
             * @todo make automatic reindex on saving configurable product
             */

            $isRelationsChanged = (bool)$product->getData('is_relations_changed');
            if ($isRelationsChanged)
            {
                $this->changeIndexStatus();
            }
        }
        elseif ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        {
            $this->updateIndex();
        }
    }

    public function onCatalogProductAttributeUpdateAfter(Varien_Event_Observer $observer)
    {
        $this->updateIndex();
    }

    private function getProcess()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_attribute');
    }

    private function changeIndexStatus()
    {
        $this->getProcess()->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
    }

    private function updateIndex()
    {
        try
        {
            $this->getProcess()->reindexEverything();
        }
        catch (Mage_Core_Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addException(
                $e,
                Mage::helper('index')->__('There was a problem with reindexing process.')
            );
        }
    }
}