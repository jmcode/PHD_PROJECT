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

class Aitoc_Aitshopassist_Model_Condition extends Mage_CatalogRule_Model_Rule_Condition_Combine
{
    public function getId()
    {
        $ruleFilterId = Mage::registry('rule_filter_id');
        if($ruleFilterId)
        {
            return $ruleFilterId;
        }
        return parent::getId();
    }
    
    public function asHtml()
    {
           $html = $this->getTypeElement()->getHtml().
               Mage::helper('aitshopassist')->__("Products with the following conditions will be displayed.");
           if ($this->getId()!='1') {
           }
        return $html;
    }
    
    public function isAllowedForRuleCondition()
    {
        $allowedInputTypes = array('text', 'textarea', 'date', 'select', 'boolean', 'price');
        return $this->getIsVisible() && in_array($this->getFrontendInput(), $allowedInputTypes);
    }
    
    public function getNewChildSelectOptions()
    {
        $allowedInputTypes = array('text', 'textarea', 'date', 'select', 'boolean', 'price');
        
        $conditions = parent::getNewChildSelectOptions();
        $attributes = array();
        
        foreach ($conditions[2]['value'] as $cond)
        {
            $conditions_list[] = $cond['label'];
        }

        $productAttributes = Mage::getResourceSingleton('catalog/product')
            ->loadAllAttributes()
            ->getAttributesByCode();
        
        $attributes = array();
        foreach ($productAttributes as $attribute) {
            if (!in_array($attribute->getFrontendInput(), $allowedInputTypes) || !in_array($attribute->getFrontendLabel(), $conditions_list))
            {
                continue;
            }
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        asort($attributes);
        $productAttributes = $attributes;
        $attributes = array();
        foreach ($productAttributes as $code=>$label) {
            $attributes[] = array('value'=>'catalogrule/rule_condition_product|'.$code, 'label'=>$label);
        }
        
        foreach($conditions as $key=>$item)
        {
            if($item['label']==Mage::helper('catalogrule')->__('Conditions Combination'))
            {
                //unset($conditions[$key]);
            }
            elseif($item['label']==Mage::helper('catalogrule')->__('Product Attribute'))
            { 
                foreach($item['value'] as $k=>$v)
                {
                    if($v['value']=='catalogrule/rule_condition_product|category_ids')
                    {
                        unset($conditions[$key]['value'][$k]);
                    }
                }
            }
        }

        return $conditions;
    }
}