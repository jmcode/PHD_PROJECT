<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('shipping_matrixdays')};
CREATE TABLE {$this->getTable('shipping_matrixdays')} (
  pk int(10) unsigned NOT NULL auto_increment,
  website_id int(11) NOT NULL default '0',
  dest_country_id varchar(4) NOT NULL default '0',
  dest_region_id int(10) NOT NULL default '0',
  dest_city varchar(10) NOT NULL default '',
  dest_zip varchar(10) NOT NULL default '',
  dest_zip_to varchar(10) NOT NULL default '',
  package_id varchar(30) NOT NULL default '',
  weight_from_value decimal(12,4) NOT NULL default '0.0000',
  weight_to_value decimal(12,4) NOT NULL default '0.0000',
  price_from_value decimal(12,4) NOT NULL default '0.0000',
  price_to_value decimal(12,4) NOT NULL default '0.0000',
  item_from_value decimal(12,4) NOT NULL default '0.0000',
  item_to_value decimal(12,4) NOT NULL default '0.0000',
  price decimal(12,4) NOT NULL default '0.0000',
  day int(1) NULL,
  algorithm varchar(255) NOT NULL default '',
  customer_group varchar(30) NOT NULL default '',
  cost decimal(12,4) NOT NULL default '0.0000',
  delivery_type varchar(255) NOT NULL default '',
  notes varchar(255) NULL,
  PRIMARY KEY(`pk`),
  UNIQUE KEY `dest_country` (`website_id`,`dest_country_id`,`dest_region_id`,`day`,`dest_zip`,`dest_zip_to`,`package_id`,`weight_from_value`,`weight_to_value`,`price_from_value`,`price_to_value`,`item_from_value`,`item_to_value`,`delivery_type`,`algorithm`,`customer_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;





ALTER TABLE {$this->getTable('sales_flat_quote_address')}  ADD dispatch_date text;
ALTER TABLE {$this->getTable('sales_flat_quote_address')}  ADD expected_delivery text;
ALTER TABLE {$this->getTable('sales_flat_quote_address')}  ADD delivery_description text;
ALTER TABLE {$this->getTable('sales_flat_quote_address')}  ADD delivery_notes text;

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD dispatch_date varchar(30);
ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD expected_delivery varchar(30);
ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD delivery_description varchar(255);
ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD delivery_notes varchar(255);



select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='order';

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'dispatch_date',
    	backend_type	= 'varchar',
    	frontend_input	= 'text';

insert ignore into {$this->getTable('eav_attribute')}
	set entity_type_id 	= @entity_type_id,
		attribute_code 	= 'expected_delivery',
		backend_type	= 'varchar',
		frontend_input	= 'text';

ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD dispatch_date varchar(30);
ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD expected_delivery varchar(30);
ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD delivery_description varchar(255);
ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD delivery_notes varchar(255);


select @attribute_set_id:=attribute_set_id from {$this->getTable('eav_attribute_set')} where entity_type_id=@entity_type_id;
select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='General' and attribute_set_id=@attribute_set_id;


select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='dispatch_date';

insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
    	attribute_set_id 	= @attribute_set_id,
    	attribute_group_id	= @attribute_group_id,
    	attribute_id		= @attribute_id;

insert ignore into {$this->getTable('catalog_eav_attribute')}
    set attribute_id 	= @attribute_id,
    	is_visible 	= 1,
    	used_in_product_listing	= 1,
    	is_filterable_in_search	= 1;

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='expected_delivery';

insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
    	attribute_set_id 	= @attribute_set_id,
    	attribute_group_id	= @attribute_group_id,
    	attribute_id		= @attribute_id;

insert ignore into {$this->getTable('catalog_eav_attribute')}
    set attribute_id 	= @attribute_id,
    	is_visible 	= 1,
    	used_in_product_listing	= 1,
    	is_filterable_in_search	= 1;

    	
select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='delivery_description';

insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
    	attribute_set_id 	= @attribute_set_id,
    	attribute_group_id	= @attribute_group_id,
    	attribute_id		= @attribute_id;

insert ignore into {$this->getTable('catalog_eav_attribute')}
    set attribute_id 	= @attribute_id,
    	is_visible 	= 1,
    	used_in_product_listing	= 1,
    	is_filterable_in_search	= 1;

    	
select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='delivery_notes';

insert ignore into {$this->getTable('eav_entity_attribute')}
    set entity_type_id 		= @entity_type_id,
    	attribute_set_id 	= @attribute_set_id,
    	attribute_group_id	= @attribute_group_id,
    	attribute_id		= @attribute_id;

insert ignore into {$this->getTable('catalog_eav_attribute')}
    set attribute_id 	= @attribute_id,
    	is_visible 	= 1,
    	used_in_product_listing	= 1,
    	is_filterable_in_search	= 1;
    	
    	
   ");

$installer->run("

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
	attribute_code 	= 'package_id',
	backend_type	= 'int',
	frontend_input	= 'select',
	is_required	= 0,
	is_user_defined	= 1,
	frontend_label	= 'Shipping Group';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='package_id';

insert ignore into {$this->getTable('catalog_eav_attribute')} 
    set attribute_id 	= @attribute_id,
    	is_visible 	= 1,
    	used_in_product_listing	= 1,
    	is_filterable_in_search	= 0;	
	
insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
	attribute_code 	= 'shipping_qty',
	backend_type	= 'int',
	frontend_input	= 'text',
	is_required	= 0,
	is_user_defined	= 1,
	frontend_label	= 'Shipping Qty';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='shipping_qty';

insert ignore into {$this->getTable('catalog_eav_attribute')} 
    set attribute_id 	= @attribute_id,
    	is_visible 	= 1,
    	used_in_product_listing	= 1,
    	is_filterable_in_search	= 0;	

");


$entityTypeId = $installer->getEntityTypeId('catalog_product');

$attributeSetArr = $installer->getConnection()->fetchAll("SELECT attribute_set_id FROM {$this->getTable('eav_attribute_set')} WHERE entity_type_id={$entityTypeId}");

$attributeId = $installer->getAttributeId($entityTypeId,'package_id');

foreach( $attributeSetArr as $attr)
{	
	$attributeSetId= $attr['attribute_set_id'];
	
	$installer->addAttributeGroup($entityTypeId,$attributeSetId,'Shipping','99');
	
	$attributeGroupId = $installer->getAttributeGroupId($entityTypeId,$attributeSetId,'Shipping');
	
	$installer->addAttributeToGroup($entityTypeId,$attributeSetId,$attributeGroupId,$attributeId,'99');	

};
    

$installer->endSetup();


