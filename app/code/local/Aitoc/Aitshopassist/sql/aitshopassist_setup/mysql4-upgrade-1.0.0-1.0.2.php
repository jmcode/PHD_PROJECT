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
/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$this->run('

    DROP TABLE IF EXISTS `' . $this->getTable('adjnav_catalog_product_index_configurable') . '`;

    CREATE TABLE `' . $this->getTable('adjnav_catalog_product_index_configurable') . '` LIKE `' . $this->getTable(Mage::helper('aitshopassist')->getBaseIndexTable()) . '`;

');

$this->endSetup();