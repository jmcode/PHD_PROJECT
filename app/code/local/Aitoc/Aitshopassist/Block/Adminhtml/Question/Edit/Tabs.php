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

class Aitoc_Aitshopassist_Block_Adminhtml_Question_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('aitshopassist_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('aitshopassist')->__('Question Information'));
    }

    protected function _beforeToHtml()
    {
        $hlp = Mage::helper('aitshopassist');
        $main = $this->getLayout()->createBlock('aitshopassist/adminhtml_question_edit_tab_main');
        $this->addTab('main', array(
            'label'     => $hlp->__('General'),
            'title'     => $hlp->__('General'),
            'content'   => $main ->toHtml()
        ));

        $this->addTab('conditions', array(
            'label'     => $hlp->__('Conditions'),
            'title'     => $hlp->__('Conditions'),
            'content'   => $this->getLayout()->createBlock('aitshopassist/adminhtml_question_edit_tab_conditions')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}