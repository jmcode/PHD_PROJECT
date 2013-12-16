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

class Aitoc_Aitshopassist_Block_Adminhtml_Question_Configure_Filter extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::getModel('aitshopassist/rule');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');
        $data = array();
        $answerId = Mage::registry('rule_filter_id');
        if($answerId>0)
        {
            $answer = Mage::getModel('aitshopassist/answer')->load($answerId);
            $condition = $answer->getConditionUnserialized();
            $model->setForm($form);
            $model->setConditions($condition);
            $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
            $condition->setRule($model);
        }
        
        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('aitshopassist/question/configure/filter.phtml')
            ->setNewChildUrl($this->getUrl('*/promo_catalog/newConditionHtml/form/rule_conditions_fieldset'));

        $fieldset = $form->addFieldset('conditions_fieldset', array(
            'legend'=>Mage::helper('catalogrule')->__('Conditions (leave blank for all products)'))
        )->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('catalogrule')->__('Conditions'),
            'title' => Mage::helper('catalogrule')->__('Conditions'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        
        if($model)
        {
            $fieldset->addField('entity_id', 'hidden', array(
                    'name' => 'entity_id',
            ));
            $form->addValues($model->getData());
        }
        $this->setForm($form);
    }
}