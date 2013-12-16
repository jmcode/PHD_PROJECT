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

class Aitoc_Aitshopassist_Helper_Catalog_Product_Composite extends Mage_Adminhtml_Helper_Catalog_Product_Composite
{
    protected function _initConfigureResultLayout($controller, $isOk, $productType)
    {
        $update = $controller->getLayout()->getUpdate();
        if(1 || $isOk) {
            $update->addHandle('AITSHOPASSIST_ADMINHTML_QUESTION_CONFIGURE');
        } else {
            $update->addHandle('ADMINHTML_CATALOG_PRODUCT_COMPOSITE_CONFIGURE_ERROR');
        }
        $controller->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
        return $this;
    }
    
    public function renderConfigureResult($controller, Varien_Object $configureResult)
    {
        if (!$configureResult->getOk()) {
            Mage::throwException($configureResult->getMessage());
        };

        $currentStoreId = (int) $configureResult->getCurrentStoreId();
        if (!$currentStoreId) {
            $currentStoreId = Mage::app()->getStore()->getId();
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId($currentStoreId)
            ->load($configureResult->getProductId());
        if (!$product->getId()) {
            Mage::throwException($this->__('Product is not loaded.'));
        }
        Mage::register('current_product', $product);
        Mage::register('product', $product);

        // Register customer we're working with
        $currentCustomer = $configureResult->getCurrentCustomer();
        if (!$currentCustomer) {
            $currentCustomerId = (int) $configureResult->getCurrentCustomerId();
            if ($currentCustomerId) {
                $currentCustomer = Mage::getModel('customer/customer')
                    ->load($currentCustomerId);
            }
        }
        if ($currentCustomer) {
            Mage::register('current_customer', $currentCustomer);
        }

        // Prepare buy request values
        $buyRequest = $configureResult->getBuyRequest();
        if ($buyRequest) {
            Mage::helper('catalog/product')->prepareProductOptions($product, $buyRequest);
        }

        $isOk = true;
        $productType = $product->getTypeId();
        
        $this->_initConfigureResultLayout($controller, $isOk, $productType);
        $controller->renderLayout();
        
    }
}