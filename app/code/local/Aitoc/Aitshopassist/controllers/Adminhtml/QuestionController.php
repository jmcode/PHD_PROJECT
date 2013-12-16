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

class Aitoc_Aitshopassist_Adminhtml_QuestionController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction() {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/aitshopassist');
        $this->_addBreadcrumb($this->__('Shopping Assistant'), $this->__('Question')); 
        $this->_addContent($this->getLayout()->createBlock('aitshopassist/adminhtml_question')); 	
     	$this->renderLayout();
    }
    
    public function newAction() {

        $this->loadLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        
//        if ( version_compare(Mage::getVersion(),'1.7.0.3','<') ) 
        
        $headBlock->addCss('aitoc/aitshopassist/popup.css');
        $headBlock->addJs('aitoc/aitshopassist/mage/adminhtml/product/composite/configure.js');        
        
        $this->_setActiveMenu('catalog/aitshopassist');
        $this->_addBreadcrumb($this->__('Shopping Assistant'), $this->__('Question')); 
        
        $this->getLayout()->createBlock('adminhtml/promo_catalog_edit');
             
        $jsBlock = $this->getLayout()->createBlock('adminhtml/template');
        $jsBlock->setTempate('promo/js.phtml');
        
        $jsOrderCreateBlock = $this->getLayout()->createBlock('adminhtml/template');
        $jsOrderCreateBlock->setTempate('sales/order/create/js.phtml');
        
        $jsBlock = $this->getLayout()->getBlock('js');
        $this->_addJs($jsOrderCreateBlock);
        
        
        $this->_addContent($this->getLayout()->createBlock('aitshopassist/adminhtml_question_edit'))
             ->_addLeft($this->getLayout()->createBlock('aitshopassist/adminhtml_question_edit_tabs'));
        
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->getLayout()->getBlock('head')->setCanLoadRulesJs(true);
        $this->renderLayout();
    }
    
    public function editAction() {
        $question_id = $this->getRequest()->getParam('entity_id');
        $question = Mage::getModel('aitshopassist/question')->load($this->getRequest()->getParam('entity_id'));
        if($question->getEntityId())
        {
            $store_id = $this->getRequest()->getParam('store', 0);
            $question->setData('store_id',$store_id);
            $question->checkStoreText();
            if(!Mage::registry('aitshopassist_question'))
            {
                Mage::register('aitshopassist_question',$question);
            }
        }
        else
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitshopassist')->__('This question no longer exists'));
            $this->_redirect('*/*/');
            return;
        }
        
        $this->loadLayout();
        $headBlock = $this->getLayout()->getBlock('head');
//        if ( version_compare(Mage::getVersion(),'1.5.0.0','<') ) 
        $headBlock->addCss('aitoc/aitshopassist/popup.css');
        $headBlock->addJs('aitoc/aitshopassist/mage/adminhtml/product/composite/configure.js');
        
        $this->_setActiveMenu('catalog/aitshopassist');
        $this->_addBreadcrumb($this->__('Shopping Assistant'), $this->__('Question')); 
        
        $switchBlock = $this->getLayout()->createBlock('adminhtml/store_switcher');
        if (!Mage::app()->isSingleStoreMode() && ($switchBlock)) {
            $switchBlock->setDefaultStoreName($this->__('Default Values'))
                ->setSwitchUrl(
                    $this->getUrl('*/*/*', array('_current'=>true, 'active_tab'=>null, 'tab' => null, 'store'=>null))
                );
        }
        
        $this->getLayout()->createBlock('adminhtml/promo_catalog_edit');
             
        $jsBlock = $this->getLayout()->createBlock('adminhtml/template');
        $jsBlock->setTempate('promo/js.phtml');
        
        $jsOrderCreateBlock = $this->getLayout()->createBlock('adminhtml/template');
        $jsOrderCreateBlock->setTempate('sales/order/create/js.phtml');
        
        $jsBlock = $this->getLayout()->getBlock('js');
        $this->_addJs($jsOrderCreateBlock);
        
        
        $this->_addContent($this->getLayout()->createBlock('aitshopassist/adminhtml_question_edit'))
             ->_addLeft($switchBlock)
             ->_addLeft($this->getLayout()->createBlock('aitshopassist/adminhtml_question_edit_tabs'));
        
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->getLayout()->getBlock('head')->setCanLoadRulesJs(true);
        $this->renderLayout();
    }
    
    public function saveAction() 
    {
        $storeId        = $this->getRequest()->getParam('store');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $isEdit         = (int)($this->getRequest()->getParam('question_id') != null);
        $question = Mage::getModel('aitshopassist/question');
        $data = $this->getRequest()->getPost();
        $data['store_id'] = $this->getRequest()->getParam('store', 0);
        $entity_id_param =  $this->getRequest()->getParam('question_id');
        try {
            if($isEdit)
            {
                $data['entity_id'] = $entity_id_param;
                if ($data) {
                    $questionEdit = $question->load($entity_id_param);
                    $questionEdit->setData($data);
                    $questionEdit->save();
                    $this->_getSession()->addSuccess($this->__('The Question has been saved.'));
                }
            }
            else{
                $question = Mage::getModel('aitshopassist/question');
                $question->setData(array('page_id'=>$data['page_id'],'text'=>$data['text'],'description'=>$data['description'],'position'=>$data['position'],'store_id'=>$data['store_id']));
                $question->save();
                if($question->getEntityId())
                {
                    $entity_id_param = $question->getEntityId();
                }
                $this->_getSession()->addSuccess($this->__('The Question has been saved.'));
            }
            if($entity_id_param)//question exist
            {   
                $answerModel = Mage::getModel('aitshopassist/answer'); 
                $answerModel->onSaveQuestion($entity_id_param,$data);
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
            $this->_redirect('aitshopassist/adminhtml_question/edit', array(
                '_current' => true,
                'entity_id' => $entity_id_param,
                'page_id' => $this->getRequest()->getParam('page_id')
            ));return;    
        }
		$this->_redirect('aitshopassist/adminhtml_page/edit',array('entity_id'=> $this->getRequest()->getParam('page_id'),'active_tab'=>'questions','store'=>$this->getRequest()->getParam('store', 0)));
    }
    
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('entity_id')) {
            $question = Mage::getModel('aitshopassist/question')
                ->load($id);
            try {
                $question->delete();
                $this->_getSession()->addSuccess($this->__('The question has been deleted.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->getResponse()
            ->setRedirect($this->getUrl('*/adminhtml_page/edit', array('entity_id'=>$this->getRequest()->getParam('page_id'))));
    }
    
    public function massDeleteAction() {
        $ids = $this->getRequest()->getParam('entity_id');
		$page_id = $this->getRequest()->getParam('page_id');
    	if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select Question(s).'));
        } else {
            if (!empty($ids)) {
                try {
                    foreach ($ids as $id) {
                        $question = Mage::getSingleton('aitshopassist/question')->load($id);
                        $question->delete();
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
            ->setRedirect($this->getUrl('aitshopassist/adminhtml_page/edit', array('entity_id'=>$page_id)));
    }
    
    public function configureToAddAction()
    {
        $id = $this->getRequest()->getParam('id');
        if(($id)&&(!Mage::registry('rule_filter_id')))
        {
            Mage::register('rule_filter_id',$id);
        }
        $html = $this->getLayout()->createBlock('aitshopassist/adminhtml_question_configure_filter')->toHtml();
        $this->getResponse()->setBody($html);        
        return;
    }
}