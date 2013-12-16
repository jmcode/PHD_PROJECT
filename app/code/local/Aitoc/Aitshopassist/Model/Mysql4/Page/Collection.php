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

class Aitoc_Aitshopassist_Model_Mysql4_Page_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitshopassist/page');
    }
    
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            
            if(strpos($sql,'GROUP BY')>0) 
            {
                $result=$this->getConnection()->fetchAll($sql, $this->_bindParams);
                $this->_totalRecords=count($result);
            }    
            else
            {
                $this->_totalRecords = $this->getConnection()->fetchOne($sql, $this->_bindParams); 
            }
         }
         return intval($this->_totalRecords);
    }
}