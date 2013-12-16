<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('sales_flat_quote_address')}  ADD expected_delivery text;

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD dispatch_date text;
ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD expected_delivery text;

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='order';

insert ignore into {$this->getTable('eav_attribute')}
	set entity_type_id 	= @entity_type_id,
		attribute_code 	= 'expected_delivery',
		backend_type	= 'varchar',
		frontend_input	= 'text';


select @attribute_set_id:=attribute_set_id from {$this->getTable('eav_attribute_set')} where entity_type_id=@entity_type_id;
select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='General' and attribute_set_id=@attribute_set_id;
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


    ");

$installer->endSetup();