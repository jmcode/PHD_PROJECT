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

class Aitoc_Aitshopassist_Adminhtml_PageController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction() {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/aitshopassist');
        $this->_addBreadcrumb($this->__('Shopping Assistant'), $this->__('Question sets')); 
        $this->_addContent($this->getLayout()->createBlock('aitshopassist/adminhtml_page')); 	
     	$this->renderLayout();
    }
    
    public function newAction() {

        $this->loadLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        $this->_setActiveMenu('catalog/aitshopassist');
        $this->_addBreadcrumb($this->__('Shopping Assistant'), $this->__('Question sets')); 
        
        $this->_addContent($this->getLayout()->createBlock('aitshopassist/adminhtml_page_edit'))
             ->_addLeft($this->getLayout()->createBlock('aitshopassist/adminhtml_page_edit_tabs'));
        
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        
        $this->renderLayout();
    }
    
    public function editAction() {
    	
    	
    	
        $page_id = $this->getRequest()->getParam('entity_id');
        $page = Mage::getModel('aitshopassist/page')->load($this->getRequest()->getParam('entity_id'));
        if($page->getEntityId())
        {
            $store_id = $this->getRequest()->getParam('store', 0);
            $page->setData('store_id',$store_id);
            $page->checkStoreText();
            if(!Mage::registry('aitshopassist_page'))
            {
                Mage::register('aitshopassist_page',$page);
            }
        }
        else
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitshopassist')->__('This question set no longer exists'));
            $this->_redirect('*/*/');
            return;
        }
        
        $this->loadLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        $this->_setActiveMenu('catalog/aitshopassist');
        $this->_addBreadcrumb($this->__('Shopping Assistant'), $this->__('Question sets')); 
        
        $switchBlock = $this->getLayout()->createBlock('adminhtml/store_switcher');
        if (!Mage::app()->isSingleStoreMode() && ($switchBlock)) {
            $switchBlock->setDefaultStoreName($this->__('Default Values'))
                ->setSwitchUrl(
                    $this->getUrl('*/*/*', array('_current'=>true, 'active_tab'=>null, 'tab' => null, 'store'=>null))
                );
        }
       
        $this->_addContent($this->getLayout()->createBlock('aitshopassist/adminhtml_page_edit'))
             ->_addLeft($switchBlock)
             ->_addLeft($this->getLayout()->createBlock('aitshopassist/adminhtml_page_edit_tabs'));
        
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        
        $this->renderLayout();
    }
    
    public function categoriesJsonAction()
    {
        $page_id = $this->getRequest()->getParam('entity_id');
        $page = Mage::getModel('aitshopassist/page')->load($this->getRequest()->getParam('entity_id'));
        if($page->getEntityId())
        {
            if(!Mage::registry('aitshopassist_page'))
            {
                Mage::register('aitshopassist_page',$page);
            }
        }

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('aitshopassist/adminhtml_page_edit_tab_categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }
    
    public function saveAction()
    {
        $storeId        = $this->getRequest()->getParam('store');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $isEdit         = (int)($this->getRequest()->getParam('entity_id') != null);
        $page = Mage::getModel('aitshopassist/page');
        
        $data = $this->getRequest()->getPost();
        
        $data['store_id'] = $this->getRequest()->getParam('store', 0);
        
        $dataToTb = array(
            'page_title'=>isset($data['page_title']) ? $data['page_title'] :"",
            'description'=>isset($data['description']) ? $data['description']:"",
            'short_description'=>isset($data['short_description']) ? $data['short_description']:"",
            'status' => isset($data['status']) ? $data['status']:"",
            'show_in_bar' => isset($data['show_in_bar']) ? $data['show_in_bar']:"",
            'store_id' => isset($data['store_id']) ? $data['store_id']:""            
        );

        if(isset($data['use_default']))
        {
            $dataToTb['use_default'] = $data['use_default'];
        }

        $entity_id_param =  $this->getRequest()->getParam('entity_id');
        try {
            if($isEdit)
            {
                $dataToTb['entity_id'] = $entity_id_param;
                if ($dataToTb['entity_id']) {
                    $pageEdit = $page->load($entity_id_param);
                    $pageEdit->setData($dataToTb);
                    $pageEdit->save();
                    $this->_getSession()->addSuccess($this->__('The Question set has been saved.'));
                }
            }
            elseif ($data) {
                $page->setData($data);
                $page->save();
                if($page->getEntityId())
                {
                    $entity_id_param = $page->getEntityId();
                }
                $this->_getSession()->addSuccess($this->__('The Question set has been saved.'));
            }
            if($entity_id_param)
            {
                $category = Mage::getModel('aitshopassist/page_category');
                $catIds_param = $dataToTb['status'] == 1 ? $this->getRequest()->getPost('category_ids') : '';
                $category->updateCategories($entity_id_param,$catIds_param); 
            }
                
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }
        if ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'entity_id'    => $entity_id_param,
                '_current'=>true
            ));return;    
        }
        $this->_redirect('*/*/');
    }
    
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('entity_id')) {
            $page = Mage::getModel('aitshopassist/page')
                ->load($id);
            try {
                $page->delete();
                $this->_getSession()->addSuccess($this->__('The question set has been deleted.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->getResponse()
            ->setRedirect($this->getUrl('*/*/', array('store'=>$this->getRequest()->getParam('store'))));
    }
    
    public function massDeleteAction() {
        $ids = $this->getRequest()->getParam('entity_id');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select Question set(s).'));
        } else {
            if (!empty($ids)) {
                try {
                    foreach ($ids as $id) {
                        $page = Mage::getSingleton('aitshopassist/page')->load($id);
                        $page->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($ids))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        
       $this->getResponse()
            ->setRedirect($this->getUrl('*/*/', array('store'=>$this->getRequest()->getParam('store'))));
    }
}