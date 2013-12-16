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

class Aitoc_Aitshopassist_Block_Adminhtml_Page_Edit_Tab_Attributes extends Mage_Adminhtml_Block_Catalog_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));
        $hlp = Mage::helper('aitshopassist');
        $page = Mage::registry('aitshopassist_page');
        $form->setDataObject($page);
        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>$hlp->__('Shopping Assistant Question set'))
        );
        $this->_addElementTypes($fieldset);
        $activeInactive = array(
            array(
                'value' => 0,
                'label' => $hlp->__('Inactive')
            ),array(
                'value' => 1,
                'label' => $hlp->__('Active')
            )            
        );
        $yesNo = array(
            array(
                'value' => 0,
                'label' => $hlp->__('No')
            ),array(
                'value' => 1,
                'label' => $hlp->__('Yes')
            )            
        );
        
        $rendererBaseFieldsetElement = Mage::getBlockSingleton('aitshopassist/adminhtml_form_renderer_fieldset_element');
        $fieldset->addField('page_title', 'text', array(
            'name' => 'page_title',
            'label' => $hlp->__('Question set Name'),
            'required' => true
        ))->setRenderer($rendererBaseFieldsetElement);
        $fieldset->addField('show_in_bar', 'select', array(
            'name' => 'show_in_bar',
            'label' => $hlp->__('Show Name in Shopping Assistant Bar on front-end'),
            'value' => 1,
            'values'=> $yesNo
        ));
        $fieldset->addField('status', 'select', array(
            'name' => 'status',
            'label' => $hlp->__('Status'),
            'value' => 1,
            'values'=> $activeInactive
        ));
        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => $hlp->__('Description'),
            'value' => $hlp->__('If you are not sure which product to choose, we can help you do that. Simply answer a few questions and we will form the list of products, which are right for you. You don\'t need to spent time researching and comparing different models. We will do it for you.'),
            'title' => $hlp->__('Description')
        ))->setRenderer($rendererBaseFieldsetElement);
        $fieldset->addField('short_description', 'textarea', array(
            'name' => 'short_description',
            'label' => $hlp->__('Short Description'),
            'value' => $hlp->__('Let us help you pick the right product!'),
            'title' => $hlp->__('Short Description')
        ))->setRenderer($rendererBaseFieldsetElement);
        if($page)
        {
            $fieldset->addField('entity_id', 'hidden', array(
                    'name' => 'entity_id',
            ));
            $form->addValues($page->getData());
        }
        $this->setForm($form);

    }
}