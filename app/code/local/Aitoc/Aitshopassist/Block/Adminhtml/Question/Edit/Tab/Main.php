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

class Aitoc_Aitshopassist_Block_Adminhtml_Question_Edit_Tab_Main extends Mage_Adminhtml_Block_Catalog_Form
{
    protected function _prepareForm()
    {
        $model = Mage::getModel('catalogrule/rule');
        
        $question = Mage::registry('aitshopassist_question');
        
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));
        
        $form->setDataObject($question);
        
        $hlp = Mage::helper('aitshopassist');
        $page = Mage::registry('aitshopassist_page');
        $rendererBaseFieldsetElement = Mage::getBlockSingleton('aitshopassist/adminhtml_form_renderer_fieldset_element');
        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>$hlp->__('Shopping Assistant Question'))
        );
                ;
        $this->_addElementTypes($fieldset);
        
        $fieldset->addField('text', 'text', array(
            'name' => 'text',
			'required' => true,
            'label' => $hlp->__('Question text'),
            'title' => $hlp->__('Question text')
        ))->setRenderer($rendererBaseFieldsetElement);
                
                

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => $hlp->__('Description'),
            'title' => $hlp->__('Description'),
        ))->setRenderer($rendererBaseFieldsetElement);
                
        $fieldset->addField('position', 'text', array(
            'name' => 'position',
            'label' => $hlp->__('Position'),
            'title' => $hlp->__('Position'),
            'class' => 'validate-digits'
        ));
        
        if (version_compare(Mage::getVersion(),'1.5.0.0','<')) 
        {
            $configureBlock = $this->getLayout()->createBlock('aitshopassist/adminhtml_catalog_product_composite_configure')->setTemplate('aitshopassist/question/configure.phtml');        

        }
        else
        {
            $configureBlock = $this->getLayout()->createBlock('adminhtml/catalog_product_composite_configure')->setTemplate('aitshopassist/question/configure.phtml');
        }  
        $optionsBlock = $this->getLayout()->createBlock('aitshopassist/adminhtml_catalog_product_attribute_edit_tab_options');
        $rendererAddAnswer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setChild('configureBlock',$configureBlock)
            ->setChild('optionsBlock',$optionsBlock)
            ->setTemplate('aitshopassist/promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('aitshopassist/promo_catalog/newConditionHtml/form/rule_conditions_fieldset'));

        $fieldsetAddAnswer = $form->addFieldset('addanswer_fieldset', array(
            'legend'=>$hlp->__('Add Answer'))
        )->setRenderer($rendererAddAnswer);
 
        
        $fieldset->addField('page_id', 'hidden', array(
			'name' => 'page_id',
			'value' => $this->getRequest()->getParam('page_id')
        ));
        $fieldset->addField('store', 'hidden', array(
			'name' => 'store',
			'value' => $this->getRequest()->getParam('store', 0)
        ));

        if($question)
        {
            $fieldset->addField('question_id', 'hidden', array(
                    'name' => 'question_id',
                    'value' => $question->getEntityId()
            ));
            $form->addValues($question->getData());
        }
        
        $this->setForm($form);
        
        return;
    }
}