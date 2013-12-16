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

class Aitoc_Aitshopassist_Block_Adminhtml_Question_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form 
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));
        
        $hlp = Mage::helper('aitshopassist');
        if (version_compare(Mage::getVersion(),'1.5.0.0','<')) 
        {
            $configureBlock = $this->getLayout()->createBlock('aitshopassist/adminhtml_catalog_product_composite_configure')->setTemplate('aitshopassist/question/configure.phtml');        
        }
        else
        {
            $configureBlock = $this->getLayout()->createBlock('adminhtml/catalog_product_composite_configure')->setTemplate('aitshopassist/question/configure.phtml');
        }
        $optionsBlock = $this->getLayout()->createBlock('aitshopassist/adminhtml_question_edit_tab_conditions_options')->setTemplate('aitshopassist/catalog/product/attribute/optionsConditionsTab.phtml');
        $rendererAddAnswer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setChild('configureBlockConditionsTab',$configureBlock)
            ->setChild('optionsBlockConditionsTab',$optionsBlock)
            ->setTemplate('aitshopassist/promo/fieldsetConditionsTab.phtml')
            ->setNewChildUrl($this->getUrl('aitshopassist/promo_catalog/newConditionHtml/form/rule_conditions_fieldset'));

        $fieldsetAddAnswer = $form->addFieldset('addanswer_fieldset', array(
            'legend'=>$hlp->__('Add Answer'))
        )->setRenderer($rendererAddAnswer);
 
        
        $this->setForm($form);
        
        return;
    }
}