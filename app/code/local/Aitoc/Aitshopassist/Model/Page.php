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

class Aitoc_Aitshopassist_Model_Page extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitshopassist/page');
    }
    
    public function getSize()
    {
        return Mage::getModel('aitshopassist/page')->getCollection()->getSize();
    }
    
    public function getQuestions()
    {
        return Mage::getModel('aitshopassist/page')->getCollection()->load();
    }
    
    public function getQuestionSetSize($category_id)
    {
        $collection = $this->getCollection();
        
        $collection
            ->getSelect()
            ->join(array('cat' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_category')), '(cat.page_id = main_table.entity_id)', array())
            ->join(array('que' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/question')), '(que.page_id = main_table.entity_id)', array())
            ->join(array('ans' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/answer')), '(ans.question_id = que.entity_id)', array())
            ->where('cat.category_id = '. $category_id)
            ->where('main_table.status = 1');

        return $collection->getSize();
    }
    
    public function getQuestionSetCollection($category_id)
    {
        $collection = $this->getCollection();
        $collection_title = $this->getCollection();
        $collection_text = $this->getCollection();
        $collection_stext = $this->getCollection();
        
        $collection
            ->getSelect()
            ->joinLeft(array('cat' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_category')), 'cat.page_id = main_table.entity_id', array())
            ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_text')), 'title.page_id = main_table.entity_id AND title.field = "page_title"', array('page_title'=>'title.text'))
            ->joinLeft(array('descr' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_text')), 'descr.page_id = main_table.entity_id AND descr.field = "description"', array('description'=>'descr.text'))
            ->joinLeft(array('sdescr' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_text')), 'sdescr.page_id = main_table.entity_id AND sdescr.field = "short_description"', array('short_description'=>'sdescr.text'))    
            ->where('cat.category_id = '. $category_id)
            ->where('IF (('. $collection_title
                ->getSelect()
                ->setPart('columns', array())
                ->columns(array())
                ->joinLeft(array('cat' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_category')), 'cat.page_id = main_table.entity_id', array())
                ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_text')), 'title.page_id = main_table.entity_id AND title.field = "page_title"', array('page_title'=>'title.text'))
                ->where('title.store_id = '. Mage::app()->getStore()->getId())
                ->where('cat.category_id = '. $category_id)
                ->limit('1')
                .') IS NOT NULL, title.store_id = '. Mage::app()->getStore()->getId() .', title.store_id = 0)'
            )
            ->where('IF (('. $collection_text
                ->getSelect()
                ->setPart('columns', array())
                ->columns(array())
                ->joinLeft(array('cat' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_category')), 'cat.page_id = main_table.entity_id', array())
                ->joinLeft(array('descr' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_text')), 'descr.page_id = main_table.entity_id AND descr.field = "description"', array('description'=>'descr.text'))
                ->where('descr.store_id = '. Mage::app()->getStore()->getId())
                ->where('cat.category_id = '. $category_id)
                ->limit('1')
                .') IS NOT NULL, descr.store_id = '. Mage::app()->getStore()->getId() .', descr.store_id = 0)'
            )
            ->where('IF (('. $collection_stext
                ->getSelect()
                ->setPart('columns', array())
                ->columns(array())
                ->joinLeft(array('cat' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_category')), 'cat.page_id = main_table.entity_id', array())
                ->joinLeft(array('sdescr' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_text')), 'sdescr.page_id = main_table.entity_id AND sdescr.field = "short_description"', array('short_description'=>'sdescr.text'))
                ->where('sdescr.store_id = '. Mage::app()->getStore()->getId())
                ->where('cat.category_id = '. $category_id)
                ->limit('1')
                .') IS NOT NULL, sdescr.store_id = '. Mage::app()->getStore()->getId() .', sdescr.store_id = 0)'
            );
        
        return $collection;
    }

    public function checkStoreText()
    {
        $data = $this->getData();
        if(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID==$data['store_id'])
        {
            $textModel = Mage::getModel('aitshopassist/page_text');
            $textModelCollection = $textModel->getCollection();
            $textModelCollection->getSelect()
                ->where('store_id = ?', $data['store_id'])
                ->where('page_id = ?', $data['entity_id']);
            foreach($textModelCollection as $textItem)
            {
                $this->setData($textItem['field'],$textItem['text']);
            }
        }
        else {
            $textModel = Mage::getModel('aitshopassist/page_text');
            $textModelCollection = $textModel->getCollection();
            $textModelCollection->getSelect()
                ->where('store_id IN  ('.$data['store_id'].','.Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID.')')
                ->where('page_id = ?', $data['entity_id']);
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
            foreach(array(0=>"page_title",1 =>"description",2 =>"short_description") as $fieldName)
            {

                $textModel = Mage::getModel('aitshopassist/page_text');
                $textModelCollection = $textModel->getCollection();
                $adapter = $textModelCollection->getSelect()->getAdapter();
                $adapter->delete($textModelCollection->getTable('aitshopassist/page_text'),
                        "`store_id` = {$data['store_id']} AND `page_id` = {$data['entity_id']} AND `field` = '".$fieldName."'"
                    ); 
                if((!isset($data['use_default']))||(isset($data['use_default'])&& !in_array($fieldName,$data['use_default'])))
                {
                    $textModel->setData(
                        array(
                            'store_id'=>$data['store_id'],
                            'page_id'=>$data['entity_id'],
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