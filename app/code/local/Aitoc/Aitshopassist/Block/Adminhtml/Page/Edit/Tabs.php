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

class Aitoc_Aitshopassist_Block_Adminhtml_Page_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('aitshopassist_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('aitshopassist')->__('Question Set Information'));
    }

    protected function _beforeToHtml()
    {
        $hlp = Mage::helper('aitshopassist');
        $activeTab = $this->getRequest()->getParam('active_tab');
        $this->addTab('main', array(
            'label'     => $hlp->__('General Information'),
            'title'     => $hlp->__('General Information'),
            'content'   => $this->getLayout()->createBlock('aitshopassist/adminhtml_page_edit_tab_attributes')->toHtml(),
            'active'    => $activeTab != 'questions' 
        ));


        $this->addTab('labels', array(
            'label'     => $hlp->__('Categories'),
            'title'     => $hlp->__('Categories'),
            'content'   => $this->getLayout()->createBlock('aitshopassist/adminhtml_page_edit_tab_categories')->toHtml(),
        ));
        $page = Mage::registry('aitshopassist_page');
        if($page)
        {
            $this->addTab('websites', array(
                'label'     => $hlp->__('Questions'),
                'title'     => $hlp->__('Questions'),
                'content'   => $this->getLayout()->createBlock('aitshopassist/adminhtml_page_edit_tab_questions')
                    ->setTemplate('aitshopassist/question/grid/container.phtml')
                    ->toHtml(),
                'active'    => $activeTab == 'questions' 
            ));
        }
        else
        {
            $this->addTab('websites', array(
                'label'     => $hlp->__('Questions'),
                'title'     => $hlp->__('Questions'),
                'content'   =>  $hlp->__('Please SAVE the Question Set to able to add Questions.'),
            ));
        }
        
        if($this->getRequest()->getParam('tab'))
        {
            $this->setActiveTab(str_replace('aitshopassist_tabs_','',$this->getRequest()->getParam('tab')));
        }        
        
        return parent::_beforeToHtml();
    }
}