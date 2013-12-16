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

class Aitoc_Aitshopassist_Block_Adminhtml_Page_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'entity_id'; 
        $this->_blockGroup = 'aitshopassist';
        $this->_controller = 'adminhtml_page';
        parent::__construct();
        
        $hlp =  Mage::helper('aitshopassist');          
        $this->_removeButton('reset');
		
        $this->_addButton('save_and_edit_button', array(
            'label'     => Mage::helper('catalog')->__('Save and Continue Edit'),
            'onclick'   => 'var template = new Template(\''.$this->getSaveAndContinueUrl().'\', /(^|.|\r|\n)({{(\w+)}})/); var url = template.evaluate({tab_id:aitshopassist_tabsJsTabs.activeTab.id}); if (document.getElementById(\'status\').value == 0) { if (confirm(\''.$hlp->__(' If you deactive the question set, it will be automatically unassigned from categories. Are you sure?').'\')) { editForm.submit(url) } } else { editForm.submit(url) }',        
            'class'     => 'save',
        ), -100);

    }

    public function getHeaderText()
    {
        $hlp =  Mage::helper('aitshopassist'); 
        $page = Mage::registry('aitshopassist_page');
        if($page)
        {
            return $page->getPageTitle();
        }
        return $hlp->__('New Question Set');
    }
    
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            'tab' => '{{tab_id}}',
            '_current' => true,
            'back'     => 'edit',
		));
    }

    public function getSaveUrl() 
    {
        return $this->getUrl('*/' . $this->_controller . '/save',array(
            'entity_id' => $this->getRequest()->getParam('entity_id'),
		));        
    }
    
}