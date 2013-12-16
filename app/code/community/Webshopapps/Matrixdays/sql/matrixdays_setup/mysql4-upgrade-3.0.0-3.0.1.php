<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('sales_flat_quote_address')}  ADD delivery_description text;
ALTER TABLE {$this->getTable('sales_flat_quote_address')}  ADD delivery_notes text;

ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD delivery_description varchar(255);
ALTER IGNORE TABLE {$this->getTable('sales_flat_quote_shipping_rate')}  ADD delivery_notes varchar(255);


ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD delivery_description varchar(255);
ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD delivery_notes varchar(255);

select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='order';

select @attribute_set_id:=attribute_set_id from {$this->getTable('eav_attribute_set')} where entity_type_id=@entity_type_id;
select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='General' and attribute_set_id=@attribute_set_id;

   	
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


    

$installer->endSetup();