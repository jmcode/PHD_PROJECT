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

class Aitoc_Aitshopassist_Model_Answer extends Mage_Rule_Model_Rule 
{
    protected $_conditionunserialized;
    private $_answerIdsWhereConditionUpdates;
    private $_answerData = array();
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitshopassist/answer');
    }
    
    public function getAnswersCollection($question_id)
    {
        $collection = $this->getCollection();
        $collection_title = $this->getCollection();
        
        $collection
            ->getSelect()
            ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/answer_text')), 'title.answer_id  = main_table.entity_id', array('title'=>'title.text'))
            ->where('IF (('. $collection_title
                ->getSelect()
                ->setPart('columns', array())
                ->columns(array())
                ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/answer_text')), 'title.answer_id = main_table.entity_id', array('title'=>'title.text'))
                ->where('title.store_id = '. Mage::app()->getStore()->getId())
                ->where('main_table.question_id = '. $question_id)
                ->limit('1')
                .') IS NOT NULL, title.store_id = '. Mage::app()->getStore()->getId() .', title.store_id = 0)'
            )
            ->where('question_id = '. $question_id)
            ->order('position ASC')
            ->order('entity_id ASC');
        
        return $collection;
    }

    public function getAnswers($question_id)
    {
        $collection = $this->getCollection();
        $collection_default = $this->getCollection();
        $collection_title = $this->getCollection();
		$answers = array();
        
        $collection_default
            ->getSelect()
            ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/answer_text')), 'title.answer_id  = main_table.entity_id', array('title'=>'title.text'))
            ->where('question_id = '. $question_id)
            ->where('title.store_id = 0')
            ->order('position ASC')
            ->order('entity_id ASC');

        $collection
            ->getSelect()
            ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/answer_text')), 'title.answer_id  = main_table.entity_id', array('title'=>'title.text'))
            ->where('IF (('. $collection_title
                ->getSelect()
                ->setPart('columns', array())
                ->columns(array())
                ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/answer_text')), 'title.answer_id = main_table.entity_id', array('title'=>'title.text'))
                ->where('title.store_id = '. Mage::app()->getStore()->getId())
                ->where('main_table.question_id = '. $question_id)
                ->limit('1')
                .') IS NOT NULL, title.store_id = '. Mage::app()->getStore()->getId() .', title.store_id = 0)'
            )
            ->where('question_id = '. $question_id)
            ->order('position ASC')
            ->order('entity_id ASC');

        foreach ($collection_default as $val_default)
        {
            $answers[$val_default->getId()] = $val_default->getTitle();
        }

        foreach ($collection as $key_default=>$val)
        {
            if ($answers[$val->getId()])
            {
                $answers[$val->getId()] = $val->getTitle();
            }
        }
        
        return $answers;
    }
    
    public function _resetConditionUnserialized($conditions=null)
    {
        if (is_null($conditions)) {
            $conditions = $this->getConditionsInstance();
        }
        $conditions->setRule($this)->setId('1')->setPrefix('conditions');
        $this->setConditionunserialized($conditions);
        return $this;
    }

    public function setConditionunserialized($conditions)
    {
        $this->_conditionunserialized = $conditions;
        return $this;
    }

    public function getConditionUnserialized()
    {
        if (empty($this->_conditionunserialized)) {
            $this->_resetConditionUnserialized();
        }
        return $this->_conditionunserialized;
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('aitshopassist/condition');
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $conditionArr = unserialize($this->getCondition());
        if (!empty($conditionArr) && is_array($conditionArr)) {
            $this->getConditionunserialized()->loadArray($conditionArr);
        }
    }

    protected function _beforeSave()
    {
        if ($this->getConditionunserialized()) {
            $this->setCondition(serialize($this->getConditionunserialized()->asArray()));
            $this->unsConditions();
        }
        parent::_beforeSave();
    
    }
    
    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);
        
        if (isset($arr['conditions']) && isset($arr['conditions'][1])) {    
            $this->getConditionunserialized()->setConditionunserialized(array())->loadArray($arr['conditions'][1]);
        }

        return $this;
    }
    
    protected function _convertFlatToRecursive(array $rule)
    {
        $arr = array();
        foreach ($rule as $key=>$value) {
            if (($key==='conditions' || $key==='actions') && is_array($value)) {
                foreach ($value as $id=>$data) {
                    $path = explode('--', $id);
                    $node =& $arr;
                    for ($i=0, $l=sizeof($path); $i<$l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = array();
                        }
                        $node =& $node[$key][$path[$i]];
                    }
                    foreach ($data as $k=>$v) {
                        $node[$k] = $v;
                    }
                }
            } else {
                /**
                 * convert dates into Zend_Date
                 */
                if (in_array($key, array('from_date', 'to_date')) && $value) {
                    $value = Mage::app()->getLocale()->date(
                        $value,
                        Varien_Date::DATE_INTERNAL_FORMAT,
                        null,
                        false
                    );
                }
                $this->setData($key, $value);
            }
        }
        return $arr;
    }
    
    private function _deleteByKey($key)
    {
        $answer = Mage::getModel('aitshopassist/answer');
        $answer->load($key);
        $answer->delete();
    }
    
    private function _deleteOptions($data)
    {
        foreach($data['option']['delete'] as $key => $deleted)
        {
            if($data['option']['delete'][$key]) 
            {
                $this->_deleteByKey($key);
            }
        }
    }
    
    private function _setWriteKey($data, $key)
    {
        $conditions = isset($data['rule']['conditions']) ? $data['rule']['conditions'] : array();
        $ansData = $this->_answerData;
        foreach($conditions as $conditionKey => $arrVal)
        {
            if(($key == $conditionKey) || (strpos($conditionKey, '-' . $key) !== false) || (strpos($conditionKey, $key . '-') !== false))
            {
                $writeKey = '1';
                if(strval($key) != strval($conditionKey))
                {
                    $conditionKeyValues = explode('--', $conditionKey);
                    if(count($conditionKeyValues) > 0)
                    {
                        $conditionKeyValues[0] = '1';
                        $writeKey = implode('--', $conditionKeyValues);
                    }
                }
                $ansData['conditions'][$writeKey] = $data['rule'][$writeKey][$conditionKey] = $arrVal;
                unset($data['rule']['conditions'][$conditionKey]);
                $this->_answerIdsWhereConditionUpdates[] = $conditionKey;
            }
            $this->_answerData = $ansData;
        }
        return $data;
    }
    
    public function onSaveQuestion($entity_id_param, $data)
    {
        if(isset($data['option']['delete']))
        {
            $this->_deleteOptions($data);
            foreach($data['option']['delete'] as $key => $deleted)
            {
                if(!$data['option']['delete'][$key]) 
                {
                    $this->_answerIdsWhereConditionUpdates = array();
                    $this->_answerData = array(
                        'question_id' => $entity_id_param, 
                        'text' => $data['option']['value'][$key][0],
                        'position' => $data['option']['order'][$key]
                    );
                    $answerDataForSavePosition = $this->_answerData;
                    $data = $this->_setWriteKey($data, $key);
                    $answer = Mage::getModel('aitshopassist/answer');                    
                    $answer->load($key);
                    
                    $notUpdatingFlag = false;
                    //some strange logic, because add data without replace when try update.
                    if($answer->getEntityId() > 0)
                    {
                        $answData = $this->_answerData;
                        $answData['entity_id'] = $key;
                        $this->_answerData = $answData;
                        $answerDataForSavePosition['entity_id'] = $key;
                        if(!in_array($key, $this->_answerIdsWhereConditionUpdates))
                        {
                            $notUpdatingFlag = true;                            
                        }
                        else
                        {
                            $answer->_resetConditionUnserialized();
                        }
                    }                    
                    if(!$notUpdatingFlag)
                    {
                        $answer->loadPost($this->_answerData);
                        $answer->setData($this->_answerData);                      
                    }
                    else
                    {
                        $answer->setData($answerDataForSavePosition);
                    }
                    $answer->save();
                    
                    if($answer->getEntityId()>0)
                    {
                        $this->_updateAnswerText($data, $answer, $key);
                    }
                    unset($answerDataForSavePosition);
                    unset($this->_answerData);
                    unset($answer);
                }
            }
        }
        Mage::getModel('aitshopassist/question_dependence')->updateDependence($data,$entity_id_param);
    }
    
    private function _updateAnswerText($commonData, $answer, $key)
    {
        $fieldName = "option[value][$key][0]";
        $bNeedUpdate = ((!isset($commonData['use_default'])) || (isset($commonData['use_default']) && !in_array($fieldName, $commonData['use_default'])));
        $data = array(
            'store_id' => $commonData['store_id'],
            'entity_id' => $answer->getEntityId(),
            'text' => $commonData['option']['value'][$key][0]
        );
        $fieldName ='text';
        $textModel = Mage::getModel('aitshopassist/answer_text');
        $textModelCollection = $textModel->getCollection();
        $adapter = $textModelCollection->getSelect()->getAdapter();
        $adapter->delete($textModelCollection->getTable('aitshopassist/answer_text'),
                "`store_id` = {$data['store_id']} AND `answer_id` = {$data['entity_id']}"
        ); 
        if($bNeedUpdate)
        {
            $textModel->setData(
                array(
                    'store_id'=>$data['store_id'],
                    'answer_id'=>$data['entity_id'],
                    'text'=>$data[$fieldName]
                    )
            );
            $textModel->save();
        }
    }
}