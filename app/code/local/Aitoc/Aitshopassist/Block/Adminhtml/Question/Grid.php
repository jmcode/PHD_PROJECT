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

class Aitoc_Aitshopassist_Block_Adminhtml_Question_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('pageGrid');
        $this->setDefaultSort('entity_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('aitshopassist/question')->getCollection();
        $select = $collection->getSelect();
        $page = Mage::registry('aitshopassist_page');
        $select->where('`main_table`.`page_id` = ?',$page->getEntityId());
      
        
        $store_id = $this->getRequest()->getParam('store', 0);
        //text join 
        $selecTextField = array();
        if(!$store_id)
        {
            $selecTextField = array('text'=>'question_text.text');
        }
        $collection->getSelect()->joinLeft(
             array('question_text' => $collection->getTable('aitshopassist/question_text')),
            "(main_table.entity_id = question_text.question_id)  AND (question_text.field = 'text') AND (question_text.store_id =  ".$store_id.")" ,$selecTextField 
        );
        if($store_id)
        {
            $collection->getSelect()->joinLeft(
                 array('question_text_def' => $collection->getTable('aitshopassist/question_text')),
                "(main_table.entity_id = question_text_def.question_id)  AND (question_text_def.field = 'text') AND (question_text_def.store_id = 0)" ,array('text'=>'IF(LENGTH(`question_text`.`text`),`question_text`.`text`,`question_text_def`.`text`)') 
            );
        }        
        $questionDependenceTableName = $collection->getTable('aitshopassist/question_dependence');
        $collection ->getSelect()->joinLeft(
            array('dependence' => $questionDependenceTableName),
                '`main_table`.`entity_id`=`dependence`.`dependence_question_id`',
                array('aOnWhichItDependceId'=>'IF(dependence.dependence_answer_id,dependence.dependence_answer_id,0)')
        );
        $answerTableName = $collection->getTable('aitshopassist/answer');
        $collection ->getSelect()->joinLeft(
                array('aOnWhichItDependceT'=>$answerTableName),
                '`dependence`.`dependence_answer_id`=`aOnWhichItDependceT`.`entity_id`',
                array('qOnWhichItDependceIds'=>new Zend_Db_Expr('group_concat(DISTINCT(`aOnWhichItDependceT`.`question_id`) SEPARATOR ",")'))
        );
        $collection->getSelect()->joinLeft(
            array('answer_table' => $collection->getTable('aitshopassist/answer')),
            '`main_table`.`entity_id`=`answer_table`.`question_id`',
            array('answerscount' =>'IF(answer_table.entity_id,COUNT(distinct(answer_table.entity_id)),0)')            
        );
        $collection ->getSelect()
            ->group('main_table.entity_id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp =  Mage::helper('aitshopassist'); 
        $this->addColumn('entity_id', array(
            'header'    => $hlp->__('Id'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'entity_id',
        ));
        $this->addColumn('text', array(
            'header'    => $hlp->__('Question'),
            'align'     => 'right',
            'index'     => 'text',
        ));

        $this->addColumn('qOnWhichItDependceIds', array(
            'header'    => $hlp->__('Depends on'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'qOnWhichItDependceIds',
        ));

        $this->addColumn('answerscount', array(
            'header'    => $hlp->__('Number of answers<br>(type "1" to filter by 0 and 1)'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'answerscount',
        ));
        
        $this->addColumn('position', array(
            'header'    => $hlp->__('Position'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'position',
        ));
    
        $page = Mage::registry('aitshopassist_page');
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getEntityId',
                'actions'   => array(
                    array(
                        'caption' => $hlp->__('Edit'),
                        'url'     => array(
                            'base'=>'*/adminhtml_question/edit',
                            'params'=>array(
								'page_id' => $page->getEntityId(),
								'store'=>$store_id = $this->getRequest()->getParam('store', 0)
							)
                        ),
                        'field'   => 'entity_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
            ));
        return parent::_prepareColumns();
    }
  
    protected function _prepareMassaction(){
        $page = Mage::registry('aitshopassist_page');
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => Mage::helper('aitshopassist')->__('Delete'),
            'url'     =>$this->getUrl('aitshopassist/adminhtml_question/massDelete',
                            array('page_id' => $page->getEntityId())
                        ),
            'confirm'  => Mage::helper('aitshopassist')->__('Are you sure?')
        ));

        return $this; 
    }
  
    public function getRowUrl($row)
    {
	    $store_id = $this->getRequest()->getParam('store', 0);
        $page = Mage::registry('aitshopassist_page');
        return $this->getUrl('*/adminhtml_question/edit', array('entity_id' => $row->getEntityId(),'page_id' => $page->getEntityId(), 'store'=> $store_id));
    }
    
    protected function _addColumnFilterToCollection($column)
    {

        if($column->getIndex() == 'answerscount')
        {

            $cond = $column->getFilter()->getCondition();
            if (isset($cond)) {
                $acount = preg_replace('/[^\d]*/', '', array_shift($cond));
                $this->getCollection()->getSelect()->having('COUNT(*) = '.(int)$acount);
            } 
        }
        elseif($column->getIndex() == 'qOnWhichItDependceIds')
        {
            $cond = $column->getFilter()->getCondition();
            if (isset($cond)) {
                $this->getCollection()->getSelect()->having('group_concat(DISTINCT(`aOnWhichItDependceT`.`question_id`) SEPARATOR ",") like \''.array_shift($cond).'\'');
            }         
        }        
        elseif($column->getIndex() == 'entity_id')
        {
            $cond = $column->getFilter()->getCondition();
            if (isset($cond)) {
                $acount = preg_replace('/[^\d]*/', '', array_shift($cond));
                $this->getCollection()->getSelect()->where('main_table.entity_id = '.(int)$acount);
            }             
        }        
        elseif($column->getIndex() == 'position')
        {
            $cond = $column->getFilter()->getCondition();
            if (isset($cond)) {
                $acount = preg_replace('/[^\d]*/', '', array_shift($cond));
                $this->getCollection()->getSelect()->where('main_table.position = '.(int)$acount);
            }             
        }
        else
        {
            return parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }
    
    
    public function getCurrentUrl($params=array())
    {
        return $this->getUrl('*/*/*', array('_current'=>true,'tab'=>'aitshopassist_tabs_websites'));
    }    
    
}