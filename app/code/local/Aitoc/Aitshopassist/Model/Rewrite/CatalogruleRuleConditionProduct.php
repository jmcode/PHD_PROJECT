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
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 04.07.13
 * Time: 9:38
 * To change this template use File | Settings | File Templates.
 */
class Aitoc_Aitshopassist_Model_Rewrite_CatalogruleRuleConditionProduct extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    public function loadAttributeOptions()
    {
        $productAttributes = Mage::getResourceSingleton('catalog/product')
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = array();
        if (Mage::app()->getRequest()->getModuleName() == 'aitshopassist')
        {
            foreach ($productAttributes as $attribute) {
                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                if (!$attribute->getData('frontend_label'))
                {
                    continue;
                }
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }
        else
        {
            foreach ($productAttributes as $attribute) {
                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                if (!$attribute->isAllowedForRuleCondition()
                    || !$attribute->getDataUsingMethod($this->_isUsedForRuleProperty)
                ) {
                    continue;
                }
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }
}