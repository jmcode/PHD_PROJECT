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

class Aitoc_Aitshopassist_Block_Adminhtml_Question_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'entity_id'; 
        $this->_blockGroup = 'aitshopassist';
        $this->_controller = 'adminhtml_question';
        parent::__construct();
        
        $rule = Mage::registry('current_promo_catalog_rule');
        
        
        $hlp =  Mage::helper('aitshopassist');          
        $this->_removeButton('reset');
		
        $this->_addButton('save_and_edit_button', array(
            'label'     => Mage::helper('catalog')->__('Save and Continue Edit'),
            'onclick'   => 'editForm.submit(\''.$this->getSaveAndContinueUrl().'\')',        
            'class'     => 'save',
        ), -100);

    }

    public function getHeaderText()
    {
        $hlp =  Mage::helper('aitshopassist');
        $page = Mage::getModel('aitshopassist/page')->load($this->getRequest()->getParam('page_id'));
        if($page->getEntityId())
        {
            $store_id = $this->getRequest()->getParam('store', 0);
            $page->setData('store_id',$store_id);
            $page->checkStoreText();
            return $page->getPageTitle();
        }
        return $hlp->__('Question');
    }
    
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit',  
            'store'=>$this->getRequest()->getParam('store')			
        ));
    }
    
    public function getBackUrl()
    {
        return $this->getUrl('aitshopassist/adminhtml_page/edit',array(
                'entity_id'=> $this->getRequest()->getParam('page_id'),
                'active_tab'=>'questions',
				'store'=>$this->getRequest()->getParam('store')
        ));
    }
    
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array($this->_objectId => $this->getRequest()->getParam($this->_objectId),'page_id'=> $this->getRequest()->getParam('page_id'),'store'=>$this->getRequest()->getParam('store')));
    }
}