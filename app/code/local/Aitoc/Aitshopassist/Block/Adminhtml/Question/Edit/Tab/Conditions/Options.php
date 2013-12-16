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

class Aitoc_Aitshopassist_Block_Adminhtml_Question_Edit_Tab_Conditions_Options extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitshopassist/catalog/product/attribute/optionsConditionsTab.phtml');
    }
    
    protected function _prepareLayout()
    {
        $this->setChild('delete_button_c_tab',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('aitshopassist')->__('Delete Condition'),
                    'class' => 'delete delete-option'
                )));
        
        
        $this->setChild('add_new_option_button_c_tab',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('aitshopassist')->__('Add Condition'),
                    'class' => 'add',
                    'id'    => 'add_new_option_button_c_tab'
                )));
        return parent::_prepareLayout();
    }
    
    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_new_option_button_c_tab');
    }
    
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button_c_tab');
    }
    
    public function getOptionValues()
    {
        $question = Mage::registry('aitshopassist_question');
        $values = array();
        $store_id = $this->getRequest()->getParam('store', 0);
        $questionModel = Mage::getModel('aitshopassist/question');
        $questionCollection = $questionModel ->getQuestionsAnsersTableByPageId($this->getRequest()->getParam('page_id'),$store_id,$this->getRequest()->getParam('entity_id'));
        $aQuestions = array();
        $aAllAnswers = array();
        $aSelectedQuestions = array();
        foreach($questionCollection as $item)
        {
            $aQuestions[$item['question_id']]['qtext'] = $item['question_text'];
            $aQuestions[$item['question_id']]['answers'][$item['answer_id']] = array('answer_text' => $item['answer_text'], 'selected'=>$item['selected']);
            $aAllAnswers[$item['answer_id']] = array('answer_text' => $item['answer_text'], 'selected'=>$item['selected'],'qid'=>$item['question_id']);
            $aSelectedQuestions[$item['question_id']] = 0;
            if($item['selected'])
            {
                $aSelectedQuestions[$item['question_id']] = 1;
            }
            
        }
        
        $questionsHtml = '';
        foreach($aQuestions as $q_id =>$q_inf)
        {
            $answersOptions ='';
            $value['intype'] = '';
            $value['store0'] = $q_inf['qtext'];
            $questionHasSelectedAndDispayedAnswer = false;
            foreach($aAllAnswers  as $answ_id => $answ_inf)
            {
                $selected=''; 
                if($answ_inf['selected']&&($answ_inf['qid'] == $q_id))
                {
                    $selected ='selected';
                    $questionHasSelectedAndDispayedAnswer = true;
                }
                
                $style ="";
                if($answ_inf['qid']!=$q_id)
                {
                    $style ='style="display:none;"';    
                }
                //$answersOptions.= '<option  class="qid_'.$answ_inf['qid'].'" '.$style.' value="'.$answ_id.'" '.$selected.' >'.$answ_inf['answer_text'].'</option>';
                $answersOptions.= '<option title="qid_'.$answ_inf['qid'].'" ' . 'class="qid_'.$answ_inf['qid'].'" '.$style.' value="'.$answ_id.'" '.$selected.' >'.$answ_inf['answer_text'].'</option>';
                                
            }

            if ($this->getRequest()->getParam('entity_id'))
            {
                $questionsOptions ='';
                foreach($aQuestions as $question_id =>$question_inf)
                { 
                    $question_id==$q_id ? $questionIsSelected='selected' : $questionIsSelected=''; 
                    $questionsOptions.= '<option value="'.$question_id.'" '.$questionIsSelected.' >'.$question_inf['qtext'].'</option>';
                }
                if($questionHasSelectedAndDispayedAnswer){
                    $value['select_questions'] = $questionsOptions;
                    $value['select_answers'] = $answersOptions;
                    $values[] = new Varien_Object($value);
                }
            }
        }
        
        return $values;
    }
    
    public function getOptionValue()
    {
        $question = Mage::registry('aitshopassist_question');
        $value = array();
        $store_id = $this->getRequest()->getParam('store', 0);
        $questionModel = Mage::getModel('aitshopassist/question');
        $questionCollection = $questionModel ->getQuestionsAnsersTableByPageId($this->getRequest()->getParam('page_id'), $store_id, $this->getRequest()->getParam('entity_id'));
        $aQuestions = array();
        foreach($questionCollection as $item)
        {
            $aQuestions[$item['question_id']]['qtext'] = $item['question_text'];
            $aQuestions[$item['question_id']]['answers'][$item['answer_id']] = array('answer_text' => $item['answer_text'], 'selected'=>$item['selected']);
        }
        $questionsHtml = '';
        $answersOptions ='';
        foreach($aQuestions as $q_id =>$q_inf)
        {
            $value['intype'] = '';
            $value['store0'] = $q_inf['qtext'];
            
            $questionHasSelectedAnswer = false;
            foreach($q_inf['answers'] as $answ_id => $answ_inf)
            {
                //$answersOptions.= '<option class="qid_'.$q_id.'" value="'.$answ_id.'" >'.$answ_inf['answer_text'].'</option>';
                $answersOptions.= '<option title="qid_'.$q_id.'" class="qid_'.$q_id.'" value="'.$answ_id.'" >'.$answ_inf['answer_text'].'</option>';
            }
            $questionsOptions ='';
            foreach($aQuestions as $question_id =>$question_inf)
            { 
                $questionsOptions.= '<option  value="'.$question_id.'"  >'.$question_inf['qtext'].'</option>';
            }
            
        }
        if(isset($questionsOptions))
        {
           $value['select_questions'] = $questionsOptions;
        }
        $value['select_answers'] = $answersOptions;
        $vObjValue = new Varien_Object($value);
        return $vObjValue;
    }
}