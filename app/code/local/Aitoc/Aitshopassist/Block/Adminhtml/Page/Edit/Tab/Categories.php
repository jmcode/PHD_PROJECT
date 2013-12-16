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

class Aitoc_Aitshopassist_Block_Adminhtml_Page_Edit_Tab_Categories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{
    public function __construct()
    {
        parent::__construct();
    }
    
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('aitshopassist/catalog/product/edit/categories.phtml');
    }
    
    public function isReadonly()
    {
       return null;
    }
    
    protected function _getNodeJson($node, $level=1)
    {
        $item = parent::_getNodeJson($node, $level);

        if (in_array($node->getId(), $this->getDisabledCategoryIds())) {
            $item['disabled'] = true;
        }
        
        return $item;
    }
    
    protected function getDisabledCategoryIds()
    {
        $page = Mage::registry('aitshopassist_page');
        $categoryIds = array();
        $category = Mage::getModel('aitshopassist/page_category');
        $categoryCollection = $category->getCollection();
        if($page)
        {
            $entity_id_param = $page->getEntityId();
            $categoryCollection->getSelect()->where('page_id != ?', $entity_id_param);
        }
        foreach($categoryCollection as $item)
        {
            $categoryIds[] = $item->getCategoryId();
        }
        
        return $categoryIds;
    }
    


    protected function getCategoryIds()
    {
        $page = Mage::registry('aitshopassist_page');
        $categoryIds = array();
        if($page)
        {
            $entity_id_param = $page->getEntityId();
            $category = Mage::getModel('aitshopassist/page_category');
            $categoryCollection = $category->getCollection();
            $categoryCollection->getSelect()->where('page_id = ?', $entity_id_param );
            foreach($categoryCollection as $item)
            {
                $categoryIds[] = $item->getCategoryId();
            }
        }
         
        return $categoryIds;
    }
    
    public function getRoot($parentNodeCategory=null, $recursionLevel=3)
    {
        if (!is_null($parentNodeCategory) && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = Mage::registry('root');
        if (is_null($root)) {
            
            $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;

            $ids = $this->getSelectedCategoriesPathIds($rootId);
            $tree = Mage::getResourceSingleton('catalog/category_tree')
                ->loadByIds($ids, false, false);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
                if ($this->isReadonly()) {
                    $root->setDisabled(true);
                }
            }
            elseif($root && $root->getId() == Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setName(Mage::helper('catalog')->__('Root'));
            }

            Mage::register('root', $root);
        }

        return $root;
    }
    
    public function getCategoryChildrenJson($categoryId)
    {
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $node = $this->getRoot($category, 1)->getTree()->getNodeById($categoryId);

        if (!$node || !$node->hasChildren()) {
            return '[]';
        }

        $children = array();
        foreach ($node->getChildren() as $child) 
        {
        	if ((string)Mage::getConfig()->getModuleConfig('Aitoc_Aitpermissions')->active == 'true') 
	        {
                    if (in_array($child->getId(), Mage::helper('aitpermissions')->getAllowedCategories()) || Mage::helper('aitpermissions')->getAllowedCategories() == array())
                    {
                        $children[] = $this->_getNodeJson($child);
                    }
	        }
	    	else
                {
                    $children[] = $this->_getNodeJson($child);
                }
        }
        
        return Mage::helper('core')->jsonEncode($children);
    }
}