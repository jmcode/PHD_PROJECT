<?php

$installer = $this;

$installer->startSetup();

if  (Mage::helper('wsalogger')->getNewVersion() > 10 ) {
	
		$packDate = array(
		        'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
		        'comment' 	=> 'MatrixDays Packing Date',
		        'nullable' 	=> 'true');

		$holdDate = array(
		        'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
		        'comment' 	=> 'MatrixDays Hold Date',
		        'nullable' 	=> 'true');

		$earliest = array(
		        'type'    	=> Varien_Db_Ddl_Table::TYPE_TEXT,
		        'comment' 	=> 'MatrixDays Earliest Delivery',
		        'nullable' 	=> 'true');	
        $installer->getConnection()->addColumn($installer->getTable('sales/order'),'delivery_holddate',$holdDate);
        $installer->getConnection()->addColumn($installer->getTable('sales/order'),'delivery_packdate',$packDate);
        $installer->getConnection()->addColumn($installer->getTable('sales/order'),'earliest',$earliest);
        $installer->getConnection()->addColumn($installer->getTable('sales/quote_address'),'earliest',$earliest);
        $installer->getConnection()->addColumn($installer->getTable('sales/quote_address_shipping_rate'),'earliest',$earliest);
} else {
		$installer->run("
			ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD delivery_holddate varchar(30) NULL COMMENT 'MatrixDays Hold Date';
			ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD delivery_packdate varchar(30) NULL COMMENT 'MatrixDays Packing Date';
			ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD earliest varchar(30) COMMENT 'MatrixDays Earliest Delivery';
			ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_address')}  ADD earliest varchar(30) COMMENT 'MatrixDays Earliest Delivery';
			ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD earliest varchar(30) COMMENT 'MatrixDays Earliest Delivery';

		");
}

$installer->endSetup();