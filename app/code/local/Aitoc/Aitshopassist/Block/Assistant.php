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

class Aitoc_Aitshopassist_Block_Assistant extends Mage_Core_Block_Template
{
    protected $_category_id;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    protected function _toHtml()
    {
        $this->setTemplate('aitshopassist/assistant.phtml');
        return parent::_toHtml();
    }
    
    public function getQuestions()
    {
        return Mage::getModel('aitshopassist/page')->getQuestions();
    }
    
    public function getCategoryId()
    {
        if (Mage::app()->getRequest()->get('cat')) 
        {
            return Mage::app()->getRequest()->get('cat');
        }
        elseif (Mage::registry('current_category'))
        {
            return Mage::registry('current_category')->getId();
        }
        elseif (Mage::app()->getRequest()->get('category_id')) 
        {
            return Mage::app()->getRequest()->get('category_id');
        }
    }
    
    public function getQuestionSetCollection($category_id)
    {
        return Mage::getModel('aitshopassist/page')->getQuestionSetCollection($category_id);
    }
    
    public function getQuestionSetSize($category_id)
    {
        return Mage::getModel('aitshopassist/page')->getQuestionSetSize($category_id);
    }
    
    public function getQuestionCollection($set_id, $category_id)
    {
        if (Mage::helper('aitshopassist')->getCurrentQuestion())
        {
            return Mage::getModel('aitshopassist/question')->getQuestionCollectionById(Mage::helper('aitshopassist')->getCurrentQuestion());
        }
        else
        {
            return Mage::getModel('aitshopassist/question')->getQuestionCollectionBySetId($set_id);
        }
    }
    
    public function getAnswersCollection($question_id)
    {
        return Mage::getModel('aitshopassist/answer')->getAnswersCollection($question_id);
    }
    
    public function getAnswers($question_id)
    {
        return Mage::getModel('aitshopassist/answer')->getAnswers($question_id);
    }
}