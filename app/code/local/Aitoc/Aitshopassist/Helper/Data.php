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

class Aitoc_Aitshopassist_Helper_Data extends Mage_Core_Helper_Abstract
{
    var $collection, $filterAttributes;    
    
    protected $_allowedBlocksToApplyFilter = array('product_list');
    
    public function allowFilter($blockNameInLayout)
    {
        return in_array($blockNameInLayout,$this->_allowedBlocksToApplyFilter);
    }
    
    public function setAnswer($category_id, $question_id, $answer_id)
    {      
        $get_name = 'getAnswerData'.$category_id;
        $set_name = 'setAnswerData'.$category_id;
        $answers = Mage::getSingleton('core/session')->$get_name();
        if ($answers)
        {
            $answers[$question_id] = $answer_id;
        }
        else 
        {
            $answers = array($question_id => $answer_id);
        }

        Mage::getSingleton('core/session')->$set_name($answers);
        return true;
    }
    
    public function getAnswer($category_id, $question_id)
    {
        $get_name = 'getAnswerData'.$category_id;
        $answers = Mage::getSingleton('core/session')->$get_name();
        return isset($answers[$question_id]) ? $answers[$question_id] : false;
    }
    
    public function unsAnswers($category_id)
    {      
        $uns_name = 'unsAnswerData'.$category_id;
        Mage::getSingleton('core/session')->$uns_name();
        return true;
    }
    
    public function getAnswers($category_id)
    {      
        $get_name = 'getAnswerData'.$category_id;
        return Mage::getSingleton('core/session')->$get_name();
    }
    
    public function getProcessAnswers()
    {    
        $answers = array();
        
        if (Mage::app()->getRequest()->get('aitanswer'))
        {
            $aitanswers = explode("_", Mage::app()->getRequest()->get('aitanswer'));

            foreach ($aitanswers as $aitanswer)
            {
                $aitanswer_id = explode("-", $aitanswer);
                
                if (isset($aitanswer_id[0]) && isset($aitanswer_id[1]) &&  (int)$aitanswer_id[0] != 0 && (int)$aitanswer_id[1] != 0)
                {
                    $answers[(int)$aitanswer_id[0]] = (int)$aitanswer_id[1];
                }
            }
        }
        
        return $answers;
    }
    
    public function getCurrentQuestion()
    {
        $aitanswerRaw = Mage::app()->getRequest()->get('aitanswer');
        if ($aitanswerRaw != '' && $aitanswerRaw != 'null')
        {
            $aitanswers = explode("_", $aitanswerRaw);
            foreach ($aitanswers as $aitanswer)
            {
                $aitanswer_id = explode("-", $aitanswer);
                return (int)$aitanswer_id[0];
            }
        }
        else
        {
            return '';
        }
    }
        
    public function getAitanswerPrevious()
    {
        if (!Mage::app()->getRequest()->get('aitanswer'))
        {
            return '';
        }

        $aitanswer = Mage::app()->getRequest()->get('aitanswer');

        $matches = array();
        preg_match_all('/(\d+)-(\d+)/', $aitanswer, $matches);

        if (!isset($matches[1]) || !isset($matches[2]))
        {
            return '';
        }

        $oldAnswers = array_combine($matches[1], $matches[2]);

        $newAnswers = array();

        $skipCurrent = true;
        $resetPrevious = true;

        foreach ($oldAnswers as $questionId => $answerId)
        {
            /*
             * in case we already answered all questions, current question has answer id
             * if we skip it, we'll return to the question before last
             * so if there is answer id, we don't skip it
             */
            if ($skipCurrent && '' == $answerId)
            {
                $skipCurrent = false;
                continue;
            }

            if ($resetPrevious)
            {
                $newAnswers[] = $questionId . '-';

                $resetPrevious = false;
                continue;
            }

            $newAnswers[] = $questionId . '-' . $answerId;
        }

        return implode('_', $newAnswers);
    }
    
    public function getQuestionAnswersIds($question_id)
    {
        $answers_collection = Mage::getModel('aitshopassist/answer')->getAnswersCollection((int)$question_id);
        
        $answers = array();
        
        foreach ($answers_collection as $answer)
        {
            $answers[] = $answer->getId();
        }
        
        return $answers;
    }
    
    public function applyAitanswerFilters($collection)
    {
        $parameter = Mage::app()->getRequest()->get('aitanswer');
        $this->collection = $collection;
        
        if (empty($parameter))
        {
            return;
        }

        $this->filterAttributes = array();

        $aitanswers = explode('_', $parameter);
        foreach ($aitanswers as $aitanswer)
        {
            $aitanswer_id = explode('-', $aitanswer);
            
            if (!isset($aitanswer_id[0]) || !isset($aitanswer_id[1]) )
            {
                continue;
            }
            
            $aitanswer_id[0] = (int) $aitanswer_id[0];
            $aitanswer_id[1] = (int) $aitanswer_id[1];
            if (0 == $aitanswer_id[1])
            {
                continue;
            }

            $answer = Mage::getModel('aitshopassist/answer')->load($aitanswer_id[1]);
            $conditions = unserialize($answer->getCondition());
            if (empty($conditions['conditions']))
            {
                continue;
            }

            $conds = $this->recursiveConditions($conditions);
            $sqlWhere = $conds ? '('.implode(') AND (',$conds).')' : '1';
            $this->collection->getSelect()->where($sqlWhere)->group('e.entity_id');
        }

        $this->filterConfigurableProducts($this->collection, $this->filterAttributes);
    }

    private function recursiveConditions($conditions)
    {
        $conds = array();
        foreach ($conditions['conditions'] as $key => $condition)
        {
            
            if ($condition['type'] == 'catalogrule/rule_condition_combine')
            {
                
                $condArr = $this->recursiveConditions($condition);

                if(count($condArr) < 1)
                    continue;

                if ($condition['aggregator'] == 'all')
                {
                    if ($condition['value'] == 0)
                    {
                        $conds[] = '0 = ('.implode(') AND 0 = (',$condArr).')';
                    } else {
                        $conds[] = '('.implode(') AND (',$condArr).')';
                    }
                } 
                elseif ($condition['aggregator'] == 'any')
                {
                    if ($condition['value'] == 0)
                    {
                        $conds[] = '0 = ('.implode(') OR 0 = (',$condArr).')';
                    } else {
                        $conds[] = '('.implode(') OR (',$condArr).')';
                    }
                }
                continue;
            }
            
            $attribute_id = Mage::getResourceModel('eav/entity_attribute')
                ->getIdByCode('catalog_product', $condition['attribute']);

            $attribute = Mage::getModel('eav/entity_attribute')->load($attribute_id);

            $select_str = $this->collection->getSelect()->__toString();

            if (strpos($select_str, 'link_table'))
            {
                continue;
            }

            $alias = 'aitattr_'.$attribute_id.'_'.$key;


            if(strpos($select_str, $alias))
            {
                $alias = $this->_getAlias($select_str, $alias);
            }


            $backend_type = $attribute->getData('backend_type');
            $frontend_input = $attribute->getData('frontend_input');

            if ($frontend_input == 'price' && $backend_type == 'decimal')
            {
                $conds[] = 'price_index.min_price '.$this->wrapValue($condition['value'], $condition['operator']);
            }
            elseif ($frontend_input == 'select' && $backend_type == 'int')
            {
                $table = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_int');

                $this->collection
                    ->getSelect()
                    ->join(array($alias => $table), $alias.'.entity_id = e.entity_id AND '.$alias . '.store_id in (0, ' . (int)Mage::app()->getStore()->getId() . ') AND '.$alias.'.attribute_id = ' . $attribute_id . ' ', array());

                $this->filterAttributes[] = array(
                    'id'    => $attribute_id,
                    'alias' => $alias,
                    'where' => array(
                        ($alias . '.store_id in (0, ' . (int)Mage::app()->getStore()->getId() . ')'),
                        ($alias . '.attribute_id = ' . $attribute_id),
                        ($alias . '.value ' . $this->wrapValue($condition['value'], $condition['operator']))
                    )
                );

                $conds[] = $alias.'.value '. $this->wrapValue($condition['value'],$condition['operator']) ;
            }
            elseif ($frontend_input == 'multiselect')
            {
                if ($condition['operator'] == '()' || $condition['operator'] == '!()')
                {
                    $this->addFilterWithAndCondition($condition['value'], $condition['attribute'], $condition['operator']);
                }
                elseif ($condition['operator'] == '!{}' || $condition['operator'] == '{}')
                {
                    $this->addFilterWithOrCondition($condition['value'], $condition['attribute'], $condition['operator']);
                }
            }
            else
            {
                $this->collection->addAttributeToFilter(
                    $condition['attribute'],
                    array(
                        $this->convertOperator($condition['operator']) =>
                        $this->wrapValueForAddFilter($condition['value'], $condition['operator'])
                    )
                );
            }
        }
        return $conds;
    }

    /*
     * params:
     * string $selected_str
     * string $alias
     */
    protected function _getAlias($selected_str, $alias)
    {
        if(strpos($selected_str, $alias))
        {
            $alias .= '_1';
            return $this->_getAlias($selected_str, $alias);
        }

        return $alias;
    }
    
    protected function addFilterWithAndCondition($condition, $attribute, $operator)
    {
        if ($operator == '()') $operator = '{}';
        if ($operator == '!()') $operator = '!{}';        

        foreach($condition as $key => $value)
        {
            $aConditions[$key] = array(
                'attribute' => $attribute,
                array(
                    $this->convertOperator($operator) => $this->wrapValueForAddFilter($value, $operator),
                )
            );

        }

        $this->collection->addAttributeToFilter($aConditions);        
    }
    
    protected function addFilterWithOrCondition($condition, $attribute, $operator)
    {
      
        foreach($condition as $value)
        {
            $this->collection->addAttributeToFilter(
                $attribute,
                array(
                    $this->convertOperator($operator) =>
                    $this->wrapValueForAddFilter($value, $operator),
                )
            );
        }        
    }
    
    public function wrapValue($value, $operator)
    {
        
        switch ($operator) {
            case '==':
                return " = '".$value."'";
                break;
            case '{}':
                return " like '%".$value."%'";
                break;
            case '!{}':
                return " not like '%".$value."%'";
                break;
            case '()':
                $values = explode(',', $value);
                foreach ($values as $k=>$v)
                {
                    $values[$k] = trim($v);
                }
                return " in ('".implode("','",$values)."')";
                break;
            case '!()':
                $values = explode(',', $value);
                foreach ($values as $k=>$v)
                {
                    $values[$k] = trim($v);
                }
                return " not in ('".implode("','",$values)."')";
                break;
            default:
                return " ".$operator." '".$value."'";
        }
    }
    
    public function wrapValueForAddFilter($value, $operator)
    {
        switch ($operator) {
            case '{}':
                return "%".$value."%";
                break;
            case '!{}':
                return "%".$value."%";
                break;
            case '()':
                $values = explode(',', $value);
                foreach ($values as $k=>$v)
                {
                    $values[$k] = trim($v);
                }
                return $values;
                break;
            case '!()':
                $values = explode(',', $value);
                foreach ($values as $k=>$v)
                {
                    $values[$k] = trim($v);
                }
                return $values;
                break;
            default:
                return $value;
        }
    }
    
    public function convertOperator($operator)
    {
        $operators = array(
            '=='  => 'eq',
            '!='  => 'neq',
            '>='  => 'gteq',
            '<='  => 'lteq',
            '>'   => 'gt',
            '<'   => 'lt',
            '{}'  => 'like',
            '!{}' => 'nlike',
            '()'  => 'in',
            '!()' => 'nin'
        );
        
        return $operators[$operator];
    }
    
    public function isAssistantOpen($category_id)
    {
        if (Mage::app()->getRequest()->get('aitanswer') != '' || $this->getCurrentQuestion())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function getQuestionSetId($category_id)
    {
        $collection = Mage::getModel('aitshopassist/page')->getQuestionSetCollection($category_id);
        
        return $collection->getFirstItem()->getId();
    }
    
    public function checkProcessStarted()
    {
        return (bool)Mage::app()->getRequest()->has('aitanswer');
    }

    public function getBaseIndexTable()
    {
        if (version_compare(Mage::getVersion(), '1.4') >= 0)
        {
            return 'catalog_product_index_eav';
        }

        return 'catalogindex_eav';
    }

    public function getProductRelationTable()
    {
        if (version_compare(Mage::getVersion(), '1.4') >= 0)
        {
            return 'catalog/product_relation';
        }

        return 'catalog/product_super_link';
    }

    public function getProductIdChildColumn()
    {
        if (version_compare(Mage::getVersion(), '1.4') >= 0)
        {
            return 'child_id';
        }

        return 'product_id';
    }

    public function hasConfigurableFix()
    {
        return (boolean)(version_compare(Mage::getVersion(), '1.4') >= 0);
    }

    public function isNewReindexAllMethod()
    {
        return (boolean)(version_compare(Mage::getVersion(), '1.4.1') >= 0);
    }

    private function filterConfigurableProducts($collection, $filterAttributes)
    {
        if (!$this->hasConfigurableFix())
        {
            return;
        }

        $adapter = $collection->getConnection();

        $simpleProducts = array();
        $removeProducts = array();
        
        $productModel         = Mage::getModel('catalog/product')->getResource();
        $attributesCount      = count($filterAttributes);

        foreach ($filterAttributes as $attribute)
        {
            $query = new Varien_Db_Select($adapter);
            $query
                ->from(array('e' => $productModel->getTable('catalog/product')), 'entity_id')
                ->join(
                    array('l' => $productModel->getTable($this->getProductRelationTable())),
                    'l.parent_id = e.entity_id',
                    array('child_id' => $this->getProductIdChildColumn())
                )
                ->join(
                    array($attribute['alias'] => $this->getConfigurableIndexerTable()),
                    $attribute['alias'] . '.entity_id = l.' . $this->getProductIdChildColumn(),
                    array()
                )
                ->where('e.type_id = ?', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);

            foreach ($attribute['where'] as $condition)
            {
                $query->where($condition);
            }

            $query
                ->join(
                    array('t_status' => $this->getStatusAttributeTable()),
                    't_status.entity_id = e.entity_id',
                    array('enabled' => 'value')
                )
                ->where('t_status.store_id in (' . (int)Mage::app()->getStore()->getId() . ', 0)')
                ->where('t_status.attribute_id = ' . $this->getStatusAttributeId());

            $query->group(
                array(
                    'e.entity_id',
                    'l.' . $this->getProductIdChildColumn(),
                    $attribute['alias'] . '.store_id'
                )
            );

            $result = $adapter->query($query);
            while ($row = $result->fetch(PDO::FETCH_ASSOC))
            {
                $simpleProducts [$row['child_id']] ['parent'] = $row['entity_id'];

                if (Mage_Catalog_Model_Product_Status::STATUS_ENABLED == $row['enabled'])
                {
                    $simpleProducts [$row['child_id']] ['attributes'] [$attribute['id']] = true;
                }
            }
        }

        foreach ($simpleProducts as $simpleProduct)
        {
            if (count($simpleProduct['attributes']) < $attributesCount)
            {
                $removeProducts[] = $simpleProduct['parent'];
            }
        }

        $removeProducts = array_unique($removeProducts);
        if (empty($removeProducts))
        {
            return;
        }

        $collection->getSelect()->where('`e`.`entity_id` NOT IN (?)', $removeProducts);
    }

    private $_statusAttributeId = null;
    private function getStatusAttributeId()
    {
        if (null == $this->_statusAttributeId)
        {
            $this->_statusAttributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'status');
        }

        return $this->_statusAttributeId;
    }

    private $_statusAttributeTable = null;
    private function getStatusAttributeTable()
    {
        if (null == $this->_statusAttributeTable)
        {
            $this->_statusAttributeTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_int');
        }

        return $this->_statusAttributeTable;
    }

    private $_configurableIndexerTable = null;
    private function getConfigurableIndexerTable()
    {
        if (null == $this->_configurableIndexerTable)
        {
            $this->_configurableIndexerTable = Mage::getResourceModel('aitshopassist/catalog_product_indexer_configurable')->getMainTable();
        }

        return $this->_configurableIndexerTable;
    }

}