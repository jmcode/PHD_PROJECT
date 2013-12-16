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

class Aitoc_Aitshopassist_Model_Question extends Mage_Core_Model_Abstract
{
    public $_questionsAnswers;
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitshopassist/question');
    }
    
    public function getQuestionCollectionById($question_id)
    {
        $collection = $this->getCollection();
        $collection_title = $this->getCollection();
        $collection_text = $this->getCollection();
        
        $collection
            ->getSelect()
            ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question_text')), 'title.question_id = main_table.entity_id AND title.field = "text"', array('title'=>'title.text'))
            ->joinLeft(array('descr' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question_text')), 'descr.question_id = main_table.entity_id AND descr.field = "description"', array('description'=>'descr.text'))
            ->where('IF (('. $collection_title
                ->getSelect()
                ->setPart('columns', array())
                ->columns(array())
                ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question_text')), 'title.question_id = main_table.entity_id AND title.field = "text"', array('page_title'=>'title.text'))
                ->where('title.store_id = '. Mage::app()->getStore()->getId())
                ->where('main_table.entity_id = '. $question_id)
                ->limit('1')
                .') IS NOT NULL, title.store_id = '. Mage::app()->getStore()->getId() .', title.store_id = 0)'
            )
            ->where('IF (('. $collection_text
                ->getSelect()
                ->setPart('columns', array())
                ->columns(array())
                ->joinLeft(array('descr' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question_text')), 'descr.question_id = main_table.entity_id AND descr.field = "description"', array('description'=>'descr.text'))
                ->where('descr.store_id = '. Mage::app()->getStore()->getId())
                ->where('main_table.entity_id = '. $question_id)
                ->limit('1')
                .') IS NOT NULL, descr.store_id = '. Mage::app()->getStore()->getId() .', descr.store_id = 0)'
            )
            ->where('entity_id = '. $question_id)
            ->order('position ASC')
            ->order('entity_id ASC')
            ->limit('1');

        return $collection;
    }
    
    public function getQuestionCollectionBySetId($set_id)
    {
        $collection = $this->getCollection();
        $collection_title = $this->getCollection();
        $collection_text = $this->getCollection();
        
        $collection
            ->getSelect()
            ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question_text')), 'title.question_id = main_table.entity_id AND title.field = "text"', array('title'=>'title.text'))
            ->joinLeft(array('descr' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question_text')), 'descr.question_id = main_table.entity_id AND descr.field = "description"', array('description'=>'descr.text'))
            ->where('IF (('. $collection_title
                ->getSelect()
                ->setPart('columns', array())
                ->columns(array())
                ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question_text')), 'title.question_id = main_table.entity_id AND title.field = "text"', array('page_title'=>'title.text'))
                ->where('title.store_id = '. Mage::app()->getStore()->getId())
                ->where('main_table.page_id = '. $set_id)
                ->limit('1')
                .') IS NOT NULL, title.store_id = '. Mage::app()->getStore()->getId() .', title.store_id = 0)'
            )
            ->where('IF (('. $collection_text
                ->getSelect()
                ->setPart('columns', array())
                ->columns(array())
                ->joinLeft(array('descr' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question_text')), 'descr.question_id = main_table.entity_id AND descr.field = "description"', array('description'=>'descr.text'))
                ->where('descr.store_id = '. Mage::app()->getStore()->getId())
                ->where('main_table.page_id = '. $set_id)
                ->limit('1')
                .') IS NOT NULL, descr.store_id = '. Mage::app()->getStore()->getId() .', descr.store_id = 0)'
            )
            ->where($this->checkPosition($set_id) ? 'main_table.position != 0' : '1')
            ->where('page_id = '. $set_id)
            ->order('position ASC')
            ->order('entity_id ASC')
            ->limit('1');
        
        return $collection;
    }
    
    public function getNextQuestionId($assistant_answers, $current_question_id, $question_set_id, $position)
    {
		$assistant_answers = (array)$assistant_answers;
        $answers_str = implode(',', $assistant_answers);
        
        $nextQuestionData = $this->_getNextQuestionData($answers_str, $current_question_id, $question_set_id, $position);
        $nextQuestionDependData = $this->_getNextQuestionDependData($answers_str, $current_question_id, $question_set_id, $position);

        if ($this->checkPosition($question_set_id))
        {
            if (($nextQuestionDependData['position_depend'] < $nextQuestionData['position'] || $nextQuestionData['position'] == '') 
                && $nextQuestionDependData['id_depend'] != '' 
                && $nextQuestionDependData['id_depend'] != $current_question_id 
                && !in_array($nextQuestionDependData['id_depend'], array_flip($assistant_answers)))
            {
                return $nextQuestionDependData['id_depend'];
            }
            else
            {
                return $nextQuestionData['id'];
            }
        }
        else
        {
            if (($nextQuestionDependData['id_depend'] < $nextQuestionData['id']  || $nextQuestionData['id'] == '') 
                && $nextQuestionDependData['id_depend'] != '' 
                && $nextQuestionDependData['id_depend'] != $current_question_id 
                && !in_array($nextQuestionDependData['id_depend'], array_flip($assistant_answers)))
            {
                return $nextQuestionDependData['id_depend'];
            }
            else
            {
                return $nextQuestionData['id'];
            }
        }
    }
    
    protected function _getNextQuestionData($answers_str, $current_question_id, $question_set_id, $position)
    {
        $collection = $this->getCollection();
        $collection_qd = Mage::getModel('aitshopassist/question_dependence')->getCollection();
        
        $collection
            ->getSelect()
            ->joinLeft(array('page' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page')), 'page.entity_id = main_table.page_id', array())
            ->joinLeft(array('qd' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question_dependence')), 'qd.dependence_question_id = main_table.entity_id', array())
            ->where($this->checkPosition($question_set_id) ? 'main_table.position > '. $position : 'main_table.entity_id > '. $current_question_id)
            ->where($this->checkPosition($question_set_id) ? 'main_table.position != 0' : '1')
            ->where('main_table.entity_id NOT IN('. 
                    $collection_qd
                        ->getSelect()
                        ->setPart('columns', array())
                        ->columns(array('main_table.dependence_question_id'))
                        ->where($answers_str != '' ? 'main_table.dependence_answer_id NOT IN (' . $answers_str . ')' : '1')
                        ->where('page.entity_id = '. $question_set_id)
            .')')
            ->where('page.entity_id = '. $question_set_id)
            ->order($this->checkPosition($question_set_id) ? 'position ASC' : 'main_table.entity_id ASC')
            ->limit('1');
        
        $next_question_id = $collection->getFirstItem()->getId();
        $next_question_position = $collection->getFirstItem()->getPosition();
        
        return array('id' => $next_question_id, 
                    'position' => $next_question_position);
    }
    
    protected function _getNextQuestionDependData($answers_str, $current_question_id, $question_set_id, $position)
    {
        $collection_depend = $this->getCollection();
        
        /** Selecting dependable options with current answers for that user have selected */
        $collection_depend
            ->getSelect()
            ->joinLeft(array('page' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page')), 'page.entity_id = main_table.page_id', array())
            ->joinLeft(array('qd' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question_dependence')), 'qd.dependence_question_id = main_table.entity_id', array())
            ->setPart('columns', array())
            ->columns(array(
                'dependence_question_id' => 'qd.dependence_question_id', 
                'cnt' => 'count( qd.dependence_question_id )',
                'position' => 'main_table.position'))
            ->where($this->checkPosition($question_set_id) ? 'main_table.position > '. $position : 'main_table.entity_id > '. $current_question_id)
            ->where($this->checkPosition($question_set_id) ? 'main_table.position != 0' : '1')
            ->where($answers_str != '' ? 'qd.dependence_answer_id IN(' . $answers_str . ')' : '1')
            ->where('page.entity_id = '. $question_set_id)
            ->where('qd.dependence_question_id IS NOT NULL')
            ->group('qd.dependence_question_id');
        
        $next_question_id_depend = '';
        $next_question_position_depend = '';
        
        foreach ($collection_depend->getData() as $key=>$val)
        {
            $collection_depend_sub = Mage::getModel('aitshopassist/question_dependence')->getCollection();
            /** Checking if this dependable options have all connection answered */
            $collection_depend_sub
                ->getSelect()
                ->joinLeft(array('a' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/answer')), 'a.entity_id = main_table.dependence_answer_id', array())
                ->joinLeft(array('q' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question')), 'q.entity_id = main_table.dependence_question_id', array())

                ->joinLeft(array('page' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page')), 'page.entity_id = q.page_id', array())

                ->setPart('columns', array())
                ->columns(array(
                    'question_id' => 'a.question_id'))
                ->where('main_table.dependence_question_id  = ' . $val['dependence_question_id'] . '')
                ->where('page.entity_id = '. $question_set_id)
                ->group('a.question_id');
            if ($collection_depend_sub->getSize() == $val['cnt'] && ($next_question_position_depend == '' || $next_question_position_depend > $val['position']))
            {
                $next_question_id_depend = $val['dependence_question_id'];
                $next_question_position_depend = $val['position'];
            }
        }
        
        return array('id_depend' => $next_question_id_depend, 
                    'position_depend' => $next_question_position_depend);
    }
    
    public function getFirstQuestionId($question_set_id)
    {
        $collection = $this->getQuestionCollectionBySetId($question_set_id);
            
        return $collection->getFirstItem()->getId();
    }
    
    public function getQuestionPosition($question_id)
    {
        $collection = $this->getCollection();
        
        $collection
            ->getSelect()
            ->where('main_table.entity_id = '. $question_id)
            ->limit('1');
        
        return $collection->getFirstItem()->getPosition();
    }

    public function getQuestionsAnsersTableByPageId($pageId,$store_id,$questionId = null)
    {
        $filterIfNotNewQuestion = '';
        
        $this->_questionsAnswers = $this->getCollection();
        
        //join store text
        $this->_questionsAnswers->getSelect()->joinLeft(
                 array('question_text' => $this->_questionsAnswers->getTable('aitshopassist/question_text')),
                "(main_table.entity_id = question_text.question_id)   AND (question_text.field = 'text')" ,array('question_text'=>'question_text.text')
            )
            ->where('IF (question_text.store_id = '. $store_id .', question_text.store_id = '. $store_id .', question_text.store_id = 0)'); 
        

        $answerTableName = $this->getCollection()->getTable('aitshopassist/answer');
        $questionDependenceTableName = $this->getCollection()->getTable('aitshopassist/question_dependence');
        
        
        if($questionId)
        {
            $filterIfNotNewQuestion  = 'AND `dependence`.`dependence_question_id`='.$questionId;
            $this->_questionsAnswers->getSelect()
                 ->where('`main_table`.`entity_id` <> ?',$questionId);
        }
        $this->_questionsAnswers->getSelect()
                
            ->where('page_id = ?',$pageId)
            ->reset('columns') 
            ->joinRight(array('answer' => $answerTableName),
                    '`main_table`.`entity_id`=`answer`.`question_id`',
                    array('question_id' =>'main_table.entity_id',
                        'question_text' => 'question_text.text', 
                        'answer_id'=>'answer.entity_id',
                        )
            )
            //join store text
            ->joinLeft(
                 array('answer_text' => $this->_questionsAnswers->getTable('aitshopassist/answer_text')),
                "(answer.entity_id = answer_text.answer_id)  " ,array('answer_text'=>'answer_text.text')
            )
            ->where('IF (answer_text.store_id = '. $store_id .', answer_text.store_id = '. $store_id .', answer_text.store_id = 0)')
            ->joinLeft(
                        array('dependence' => $questionDependenceTableName),
                        '`answer`.`entity_id`=`dependence`.`dependence_answer_id`'.$filterIfNotNewQuestion,
                        array('selected'=>'IF(`dependence`.`entity_id`,1,0)')
                    );

        return $this->_questionsAnswers;
    }

    public function checkPosition($question_set_id)
    {
        $collection = $this->getCollection();
        
        $collection
            ->getSelect()
            ->where('main_table.position != 0')
            ->where('page_id = '. $question_set_id);
        
        if ($collection->getSize())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function checkStoreText()
    {
        $data = $this->getData();
        if(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID==$data['store_id'])
        {
            $textModel = Mage::getModel('aitshopassist/question_text');
            $textModelCollection = $textModel->getCollection();
            $textModelCollection->getSelect()
                ->where('store_id = ?', $data['store_id'])
                ->where('question_id = ?', $data['entity_id']);
            foreach($textModelCollection as $textItem)
            {
                $this->setData($textItem['field'],$textItem['text']);
            }
        }
        else {
            $textModel = Mage::getModel('aitshopassist/question_text');
            $textModelCollection = $textModel->getCollection();
            $textModelCollection->getSelect()
                ->where('store_id IN  ('.$data['store_id'].','.Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID.')')
                ->where('question_id = ?', $data['entity_id']);
            $dataToAdd = array();
            foreach($textModelCollection as $textItem)
            {
                if($textItem['store_id']!=Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
                {
                    $dataToAdd[$textItem['field']] = $textItem['text'];
                    $this->setData($textItem['field'],$textItem['text']);
                }
                else
                {
                    $dataToAdd[$textItem['field'].'_default_value'] = $textItem['text'];
                }
            }
            foreach($textModelCollection as $textItem)
            {
                if($textItem['store_id']==Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
                {
                    if(!isset($dataToAdd[$textItem['field']]))
                    {
                        $this->setData($textItem['field'],$dataToAdd[$textItem['field'].'_default_value']);
                        $this->setData($textItem['field'].'_useDefaultValues',1);
                    }
                }
            }
        }
    }
    
    protected function _afterSave()
    {
        $data = $this->getData();
        if(isset($data['entity_id'])&& !empty($data['entity_id']))
        {
            foreach(array(0=>"text",1 =>"description") as $fieldName)
            {

                $textModel = Mage::getModel('aitshopassist/question_text');
                $textModelCollection = $textModel->getCollection();
                $adapter = $textModelCollection->getSelect()->getAdapter();
                $adapter->delete($textModelCollection->getTable('aitshopassist/question_text'),
                        "`store_id` = {$data['store_id']} AND `question_id` = {$data['entity_id']} AND `field` = '".$fieldName."'"
                    ); 
                if((!isset($data['use_default']))||(isset($data['use_default'])&& !in_array($fieldName,$data['use_default'])))
                {
                    $textModel->setData(
                        array(
                            'store_id'=>$data['store_id'],
                            'question_id'=>$data['entity_id'],
                            'field'=>$fieldName,
                            'text'=>$data[$fieldName]
                            )
                    );
                    $textModel->save();
                }
                unset($textModel);
                unset($textModelCollection);
            }              
        }
        return parent::_afterSave();
    }
}