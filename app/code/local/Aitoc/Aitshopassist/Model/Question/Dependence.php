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

class Aitoc_Aitshopassist_Model_Question_Dependence extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitshopassist/question_dependence');
    }
    
    public function checkQuestionDependenceByAnswerId($answers)
    {      
        $answers_str = implode(',', $answers);
        
        $collection = $this->getCollection();
        
        $collection
            ->getSelect()
            ->setPart('columns', array())
            ->columns(array('dependence_question_id' => 'dependence_question_id', 'cnt' => 'count( dependence_question_id )'))
            ->where('dependence_answer_id IN(' . $answers_str . ')')
            ->group('dependence_question_id');
            
        foreach ($collection->getData() as $key=>$val)
        {
            $collection_sub = $this->getCollection();
            
            $collection_sub
                ->getSelect()
                ->setPart('columns', array())
                ->columns(array('dependence_question_id' => 'dependence_question_id', 'cnt' => 'count( dependence_question_id )'))
                ->where('dependence_question_id  = ' . $val['dependence_question_id'] . '')
                ->group('dependence_question_id')
                ->limit(1);
            
            $data = $collection_sub->getData();

            if ($data[0]['cnt'] == $val['cnt'])
            {
                return true;
            }
        }
        
        return false;
    }
    
    public function checkQuestionDependenceByQuestionId($question_id)
    {      
        $collection = $this->getCollection();
        
        $collection
            ->getSelect()
            ->join(array('a' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/answer')), '(a.entity_id = main_table.dependence_answer_id)', array())
            ->where('a.question_id = ' . $question_id );
        
        if ($collection->getSize())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function getDependenceAnswersCollection($question_id)
    {      
        $collection = $this->getCollection();
        
        $collection
            ->getSelect()
            ->where('dependence_question_id = ' . $question_id );
        
        if ($collection->getSize())
        {
            return $collection;
        }
        else
        {
            return false;
        }
    }

    public function updateDependence($data, $q_id)
    {
        $collection = $this->getCollection();
        $collection
            ->getSelect()
            ->where('`main_table`.`dependence_question_id` = ?', $q_id);
       
        if (isset($data['ctaboption']))
        {
            $answersMustBeInDb = array();
            if(isset($data['ctaboption']['answer']))
            {
                foreach ($data['ctaboption']['answer'] as $optionKey => $answIds)
                {
                    if (!$data['ctaboption']['delete'][$optionKey])
                    {
                        foreach ($answIds as $answId)
                        {
                            $answersMustBeInDb[] = $answId;    
                        }
                    }
                }
            }
            $answersAlreadyInDb = array();
            foreach ($collection->getItems() as $key => $item)
            {
                if (!in_array($item->getData('dependence_answer_id'), $answersMustBeInDb))
                {
                    $item->delete();
                }
                else
                {
                    $answersAlreadyInDb[] = $item->getData('dependence_answer_id');
                }
            }
            $answersNeedToAdd = array_diff($answersMustBeInDb, $answersAlreadyInDb);

            if (count($answersNeedToAdd))
            {
                foreach ($answersNeedToAdd as $a_id)
                {
                    $modelToSave = Mage::getModel('aitshopassist/question_dependence');
                    $modelToSave->setDependenceAnswerId($a_id);
                    $modelToSave->setDependenceQuestionId($q_id);
                    $modelToSave->save();
                    unset($modelToSave);
                }
            }
        }
    }
}