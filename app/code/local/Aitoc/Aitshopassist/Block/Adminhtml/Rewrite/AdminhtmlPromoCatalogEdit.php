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
class Aitoc_Aitshopassist_Block_Adminhtml_Rewrite_AdminhtmlPromoCatalogEdit extends Mage_Adminhtml_Block_Promo_Catalog_Edit
{
    public function __construct()
    {
        if(false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'aitshopassist'))
        {
            if(version_compare(Mage::getVersion(), '1.7.0.0', '<') )
	        {
                $this->_objectId = 'id';
                $this->_controller = 'promo_catalog';   

                Mage_Adminhtml_Block_Widget_Form_Container::__construct();
				
				$this->_updateButton('save', 'label', Mage::helper('catalogrule')->__('Save Rule'));
				
				$this->_addButton('save_apply', array(
                    'class'=>'save',
                    'label'=>Mage::helper('catalogrule')->__('Save and Apply'),
                    'onclick'=>"$('rule_auto_apply').value=1; editForm.submit()",
                ));
                $this->_addButton('save_and_continue', array(
                    'label'     => Mage::helper('catalogrule')->__('Save and Continue Edit'),
                    'onclick'   => 'saveAndContinueEdit()',
                    'class' => 'save'
                    ), 10);
                $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'back/edit/') } ";
				
				return;
			}
        }
		
		parent::__construct();
	}
}