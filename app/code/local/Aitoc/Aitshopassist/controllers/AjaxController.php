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

class Aitoc_Aitshopassist_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function resetAction()
    {
        $category_id = (int) Mage::app()->getRequest()->get('category_id');
        Mage::helper('aitshopassist')->unsAnswers($category_id);
        $data = array();

        $ajaxResponse = Mage::helper('core')->jsonEncode($data);
        $this->getResponse()->setBody($ajaxResponse);
    }
    
    public function previousAction()
    {
        $data = array();
        
        $data['aitanswer_previous'] = Mage::helper('aitshopassist')->getAitanswerPrevious();
        
        if (!$data['aitanswer_previous'])
        {
            $data['error_message'] = Mage::helper('aitshopassist')->__('This is the first question.');
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
    }
    
    public function nextAction()
    {
        $data = array();
        
        $assistant_answer_id = (int)Mage::app()->getRequest()->get('assistant_answer');
        if (0 == $assistant_answer_id)
        {
            $data['error_message'] = Mage::helper('aitshopassist')->__('Please choose an answer.');
            $this->_sendResponse($data);
			return;
        }
        
        $category_id = (int) Mage::app()->getRequest()->get('category_id');
        
        $question_set_id = Mage::helper('aitshopassist')->getQuestionSetId($category_id);
        
		if ( !$question_set_id )
		{
		    $data['error_message'] = Mage::helper('aitshopassist')->__('Please choose an answer.');
			$this->_sendResponse($data);
			return;
		}
		
        if (Mage::helper('aitshopassist')->getCurrentQuestion() != '')
        {
            $current_question_id = Mage::helper('aitshopassist')->getCurrentQuestion();
        }
        else 
        {
            $current_question_id = Mage::getModel('aitshopassist/question')->getFirstQuestionId($question_set_id);
        }
        
        $position = Mage::getModel('aitshopassist/question')->getQuestionPosition($current_question_id);
        
        Mage::helper('aitshopassist')->setAnswer($category_id, $current_question_id, $assistant_answer_id);

        $process_answers = Mage::helper('aitshopassist')->getProcessAnswers();

        $process_answers[$current_question_id] = $assistant_answer_id;

        $next_question_id = Mage::getModel('aitshopassist/question')->getNextQuestionId($process_answers, $current_question_id, $question_set_id, $position);

        $data['current_question_id'] = $current_question_id;
        $data['next_question_id'] = isset($next_question_id) ? $next_question_id : $current_question_id;
        $data['answers'] = Mage::helper('aitshopassist')->getQuestionAnswersIds($current_question_id);
        
        $ajaxResponse = Mage::helper('core')->jsonEncode($data);
        $this->getResponse()->setBody($ajaxResponse);
    }
    
    public function skipAction()
    {
        $data = array();
        
        $category_id = (int) Mage::app()->getRequest()->get('category_id');
        
        $question_set_id = Mage::helper('aitshopassist')->getQuestionSetId($category_id);
        
        if ( !$question_set_id )
		{
		    $data['error_message'] = Mage::helper('aitshopassist')->__('Please choose an answer.');
			$this->_sendResponse($data);
			return ;
		}

        if (Mage::helper('aitshopassist')->getCurrentQuestion())
        {
            $current_question_id = Mage::helper('aitshopassist')->getCurrentQuestion();
        }
        else 
        {
            $current_question_id = Mage::getModel('aitshopassist/question')->getFirstQuestionId($question_set_id);
        }
        
        $position = Mage::getModel('aitshopassist/question')->getQuestionPosition($current_question_id);
        
        $next_question_id = Mage::getModel('aitshopassist/question')->getNextQuestionId(array(''), $current_question_id, $question_set_id, $position);
        
        if (!Mage::getModel('aitshopassist/question_dependence')->checkQuestionDependenceByQuestionId($current_question_id))
        {
            $process_answers = Mage::helper('aitshopassist')->getProcessAnswers();
            
            $next_question_id = Mage::getModel('aitshopassist/question')->getNextQuestionId($process_answers, $current_question_id, $question_set_id, $position);
        }
        else
        {
            $data['error_message'] = Mage::helper('aitshopassist')->__('Unfortunately you can not skip this question as the answer is affects to the next question.');
        }
        
        if (!$next_question_id)
        {
            $next_question_id = $current_question_id;
        }
        
        $data['current_question_id'] = $current_question_id;
        $data['next_question_id'] = $next_question_id;
        
        $ajaxResponse = Mage::helper('core')->jsonEncode($data);
        $this->getResponse()->setBody($ajaxResponse);
    }
	
	protected function _sendResponse($data)
	{
        $ajaxResponse = Mage::helper('core')->jsonEncode($data);
        $this->getResponse()->setBody($ajaxResponse);
	}
	    
}