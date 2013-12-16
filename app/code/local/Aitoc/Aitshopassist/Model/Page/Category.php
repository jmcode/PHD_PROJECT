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

class Aitoc_Aitshopassist_Model_Page_Category extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitshopassist/page_category');
    }
    
    public function updateCategories($entity_id_param,$catIds_param,$storeId = 0)
    {
        //check if catId not in other page
        $hlp = Mage::helper('aitshopassist');
        $categoryCollection = $this->getCollection();
        if($catIds_param){
            if(strpos($catIds_param,',')===0)
            {
                $catIds_param = substr($catIds_param,1);
            }
            $categoryCollection->getSelect()
                ->where('main_table.page_id != ?', $entity_id_param )
                ->where('category_id IN('.$catIds_param.')')
                ->joinLeft(array('title' => Mage::getSingleton('core/resource')->getTableName('aitshopassist/page_text')), 'title.page_id = main_table.page_id AND title.field = "page_title"', array('page_title'=>'title.text'))
                ->where('IF (title.store_id = '. $storeId .', title.store_id = '. $storeId .', title.store_id = 0)');
            if(count($categoryCollection))
            {
                $alreadyInOtherPages = array();
                $message ='';
                foreach($categoryCollection as $item)
                {
                    $alreadyInOtherPages[] = 
                            array(
                                'categoryName' => Mage::getModel('catalog/category')->load($item->getCategoryId())->getName(),
                                'pageTitle' => $item->getPageTitle()
                            );
                    $categoryName = Mage::getModel('catalog/category')->load($item->getCategoryId())->getName();
                    $pageTitle = $item->getPageTitle();
                    $message = $message.$hlp->__("Category '%s' is already assigned for question set '%s'",$categoryName,$pageTitle)."<br>";
                }
                throw new Mage_Core_Exception($message);
            }

        }
		unset($categoryCollection);
        //update ids
        $categoryCollection = $this->getCollection();
        $categoryCollection->getSelect()->where('page_id = ?', $entity_id_param );
        
        $catIds =explode(',',$catIds_param);
        $catIds = array_unique($catIds);
        $aAlreadyHere = array();
        $toDelete = array();
        foreach($categoryCollection as $item)
        {
            if(in_array($item->getCategoryId(),$catIds))
            {
                $aAlreadyHere[] = $item->getCategoryId();
            }
            else
            {
                $toDelete[] = $item->getCategoryId();
            }
        }

        // toDelete and $catIds = array_diff($catIds,$aAlreadyHere) require to check count products
        
        $catIds = array_diff($catIds,$aAlreadyHere);
        $aRelatedData = array();
        $aRelatedData['add'] = $catIds;
        $aRelatedData['uncheck'] = $toDelete;
        //string added owing to the conflict with AITOC_COMMENT markers and block comment tags
        
	/* {#AITOC_COMMENT_END#}
        if (!Aitoc_Aitsys_Abstract_Service::get()->getRuler('Aitoc_Aitshopassist')->checkAssignCategoriesAllow($aRelatedData)) {
            $message = "";
            foreach (Aitoc_Aitsys_Abstract_Service::get()->getRuler('Aitoc_Aitshopassist')->getErrors() as $error) {
                if($message!="") 
                {
                    $message = $message."<br>";
                }
                $message = $error;
            }
            throw new Mage_Core_Exception($message);
            return;
        }
        {#AITOC_COMMENT_START#} */
        
        if(count($toDelete))
        {
            $toDelete = array_unique($toDelete);
            $sToDelete = implode(",", $toDelete);

            $adapter = $categoryCollection->getSelect()->getAdapter();
            $adapter->delete($categoryCollection->getTable('aitshopassist/page_category'),"(`page_id`={$entity_id_param} AND (`category_id` IN({$sToDelete})))");
        }

        foreach($catIds as $catId)
        {   if($catId>0)
            {
                $data = array('page_id'=>$entity_id_param,'category_id'=>$catId);
                $this->setData($data);
                $this->save();
            }
        }
    }
}