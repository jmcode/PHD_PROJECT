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

class Aitoc_Aitshopassist_Block_Adminhtml_Page_Edit_Tab_Questions extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_question';
        $this->_blockGroup = 'aitshopassist';
        $this->_headerText = '';
        $this->_addButtonLabel = Mage::helper('aitshopassist')->__('Create new Question');
        parent::__construct();
    }
    public function getCreateUrl()
    {
        $page = Mage::registry('aitshopassist_page');
        return $this->getUrl('*/adminhtml_question/new',array('page_id' => $page->getEntityId()));
    }
}