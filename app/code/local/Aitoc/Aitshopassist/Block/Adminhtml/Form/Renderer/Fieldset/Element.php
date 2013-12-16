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

class Aitoc_Aitshopassist_Block_Adminhtml_Form_Renderer_Fieldset_Element extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element
{
    public function canDisplayUseDefault()
    {
        if ($this->getDataObject()
                && $this->getDataObject()->getEntityId()
                && $this->getDataObject()->getStoreId()) {
                return true;
        }
        return false;
    }
    
    public function usedDefault()
    {
        $fieldName = $this->getElement()->getName();
        $defaultValue = $this->getDataObject()->getData($fieldName.'_useDefaultValues');
        if($defaultValue)
        {
            return true;
        }
        return false;  
    }
    
    public function getScopeLabel()
    {
        $html = '';
        if (Mage::app()->isSingleStoreMode()) {
            return $html;
        }
        $html.= '[STORE VIEW]';
        return $html;
    }
    
    public function getAttributeCode()
    {
        return $this->getElement()->getName();
    }
}