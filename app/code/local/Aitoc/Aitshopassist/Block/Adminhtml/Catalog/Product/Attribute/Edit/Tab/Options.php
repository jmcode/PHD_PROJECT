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

class Aitoc_Aitshopassist_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Options extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitshopassist/catalog/product/attribute/options.phtml');
    }
    
    protected function _prepareLayout()
    {
        $this->setChild('add_button_custom_m_tab',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('aitshopassist')->__('Add Answer'),
                    'class' => 'add',
                    'id'    => 'add_new_option_button'
                )));
        return parent::_prepareLayout();
    }
    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_button_custom_m_tab');
    }
    
    public function getOptionValues()
    {
        $question = Mage::registry('aitshopassist_question');
        $values = array();
        if($question)
        {
            $store_id = $question->getStoreId();
            $answerCollection = Mage::getModel('aitshopassist/answer')->getCollection();
            //join store text
            $answerCollection->getSelect()->joinLeft(
                 array('answer_text' => $answerCollection->getTable('aitshopassist/answer_text')),
                "(main_table.entity_id = answer_text.answer_id) AND (answer_text.store_id =".$store_id.") " ,array('text'=>'answer_text.text')
            ); 
            if($store_id)
            {                            
            	//join store text
                $answerCollection->getSelect()->joinLeft(
                     array('def_answer_text' => $answerCollection->getTable('aitshopassist/answer_text')),
                    "(main_table.entity_id = def_answer_text.answer_id) AND (def_answer_text.store_id = 0 ) " ,array('def_text'=>'def_answer_text.text')
                );
            }
            $answerCollection->getSelect()->where('question_id = ?',$question->getEntityId());
            
            foreach($answerCollection as $item)
            {
                $item->setStoreId($question->getStoreId());
                $this->setDataObject($item);
                $value['intype'] = '';
                $value['id'] = $item->getEntityId();
                $value['sort_order'] = $item->getPosition();
                
                $value['use_default'] = '';
                $value['class_disabled'] = '';
                $value['disabled']='';
                if($item->getText())
                {
                    $value['store0'] = $this->htmlEscape($item->getText() );
                }
                elseif($store_id)
                {
                    $value['store0'] = $this->htmlEscape( $item->getDefText() );
                    $value['use_default']='checked="checked"';
                    $value['class_disabled']=' disabled';
                    $value['disabled']='disabled="disabled"';
                }
                

                $values[] = new Varien_Object($value);
                unset($value);
            }
        }
        return $values;
    }
    
    public function canDisplayUseDefault()
    {
        $question = Mage::registry('aitshopassist_question');
        if($question && $question->getStoreId())
        {
            return true;
        }
        return false;
    }
    
    
    public function usedDefault()
    {
        return false;
    }
    
    public function getScopeLabel()
    {
        $html = '';
        if (Mage::app()->isSingleStoreMode()) {
            return $html;
        }
        $html.= '[STORE VIEW]';
        return $html;
    }
}