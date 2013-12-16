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

class Aitoc_Aitshopassist_Block_Adminhtml_Page_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('pageGrid');
        $this->setDefaultSort('entity_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('aitshopassist/page')->getCollection();
        $sel = $collection->getSelect();
        
        //text join set page_title
        $collection->getSelect()->joinLeft(
             array('page_title' => $collection->getTable('aitshopassist/page_text')),
            "(main_table.entity_id = page_title.page_id) AND (page_title.store_id =  ".Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID.") AND (page_title.field = 'page_title') " ,array('text'=>'page_title.text')
        );
        
        
        $collection->getSelect()->joinLeft(
            array('additional_table' => $collection->getTable('aitshopassist/question')),
            'main_table.entity_id=additional_table.page_id',array('question_id'=>'additional_table.entity_id')
        );
        
        if ((string)Mage::getConfig()->getModuleConfig('Aitoc_Aitpermissions')->active == 'true' && Mage::helper('aitpermissions')->getAllowedCategories() != array()) 
        {
        	$collection->getSelect()->joinLeft(
	            array('pc' => $collection->getTable('aitshopassist/page_category')),
	            'main_table.entity_id=pc.page_id',array('question_id'=>'additional_table.entity_id')
	        )
	        ->where('pc.category_id IN('.implode(',', Mage::helper('aitpermissions')->getAllowedCategories()).')')
	        ;
        }
        
        $collection ->getSelect()
            ->from(null,array('questionscount' =>'IF(additional_table.entity_id,COUNT(*),0)'))
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
        $this->addColumn('page_title', array(
            'header'    => $hlp->__('Question set name'),
            'align'     => 'right',
            'index'     => 'text',
        ));
        $this->addColumn('questionscount', array(
            'header'    => $hlp->__('Number of questions<br>(type "1" to filter by 0 and 1)'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'questionscount',
        ));
        $this->addColumn('status', array(
            'header'    => $hlp->__('Status'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'options',
            'options'   => array(
                '0' 	=> $hlp->__('Inactive'),
                '1' 	=> $hlp->__('Active'),
            ),
            'index'     => 'status',
        ));
    
    
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
                            'base'=>'*/*/edit',
                            'params'=>array()
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
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('aitshopassist')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('aitshopassist')->__('Are you sure?')
        ));

        return $this; 
    }
  
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('entity_id' => $row->getEntityId()));
    }

    protected function _addColumnFilterToCollection($column)
    {
        
        if($column->getIndex() == 'questionscount')
        {
            $cond = $column->getFilter()->getCondition();
            if (isset($cond)) {
                $qcount = preg_replace('/[^\d]*/', '', array_shift($cond));
                $this->getCollection()->getSelect()->having('COUNT(*) = '.(int)$qcount);
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
        else
        {
            return parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }         
    
}