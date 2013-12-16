<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->dropIndex($installer->getTable('shipping_matrixdays'), 'dest_country');

if  (Mage::helper('wsalogger')->getNewVersion() > 10 ) {
		$volumeFrom = array(
		        'type'    	=> Varien_Db_Ddl_Table::TYPE_DECIMAL,
		        'comment' 	=> 'MatrixDays Volume From',
		        'default'   => '0.0000',
		        'scale'    => '4',
		        'precision' => '12',
		        'nullable' 	=> false);

		$volumeTo = array(
		        'type'    	=> Varien_Db_Ddl_Table::TYPE_DECIMAL,
		        'comment' 	=> 'MatrixDays Volume To',
		        'default'   => '0.0000',
		        'scale'    => '4',
		        'precision' => '12',
		        'nullable' 	=> false);

        $installer->getConnection()->addColumn($installer->getTable('shipping_matrixdays'),'volume_from_value',$volumeFrom);
        $installer->getConnection()->addColumn($installer->getTable('shipping_matrixdays'),'volume_to_value',$volumeTo);
} else {
		$installer->run("
			ALTER IGNORE TABLE {$this->getTable('shipping_matrixdays')}  ADD volume_from_value decimal(12,4) NOT NULL COMMENT 'MatrixDays Volume From';
			ALTER IGNORE TABLE {$this->getTable('shipping_matrixdays')}  ADD volume_to_value decimal(12,4) NOT NULL COMMENT 'MatrixDays Volume To';
		");
}

$installer->run("
 select @attribute_set_id:=attribute_set_id from {$this->getTable('eav_attribute_set')} where entity_type_id=@entity_type_id;
 select @attribute_group_id:=attribute_group_id from {$this->getTable('eav_attribute_group')} where attribute_group_name='General' and attribute_set_id=@attribute_set_id;
 select @entity_type_id:=entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code='catalog_product';

 insert ignore into {$this->getTable('eav_attribute')}
	set entity_type_id 	= @entity_type_id,
		attribute_code 	= 'ship_height',
		backend_type	= 'decimal',
		frontend_input	= 'text',
		is_required	= 0,
		is_user_defined	= 1,
		frontend_label	= 'Height';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='ship_height';

insert ignore into {$this->getTable('catalog_eav_attribute')}
	set attribute_id 	= @attribute_id,
		is_visible 	= 1,
		used_in_product_listing	= 1,
		is_filterable_in_search	= 1;

insert ignore into {$this->getTable('eav_attribute')}
    set entity_type_id 	= @entity_type_id,
		attribute_code 	= 'ship_width',
		backend_type	= 'decimal',
	    frontend_input	= 'text',
		is_required	= 0,
		is_user_defined	= 1,
		frontend_label	= 'Width';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='ship_width';

insert ignore into {$this->getTable('catalog_eav_attribute')}
	set attribute_id 	= @attribute_id,
    	is_visible 	= 1,
		used_in_product_listing	= 1,
		is_filterable_in_search	= 1;

insert ignore into {$this->getTable('eav_attribute')}
	set entity_type_id 	= @entity_type_id,
    	attribute_code 	= 'ship_depth',
		backend_type	= 'decimal',
		frontend_input	= 'text',
		is_required	= 0,
		is_user_defined	= 1,
		frontend_label	= 'Depth';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='ship_depth';

insert ignore into {$this->getTable('catalog_eav_attribute')}
    set attribute_id 	= @attribute_id,
		is_visible 	= 1,
		used_in_product_listing	= 1,
		is_filterable_in_search	= 1;

insert ignore into {$this->getTable('eav_attribute')}
	set entity_type_id 	= @entity_type_id,
		attribute_code 	= 'volume_weight',
		backend_type	= 'decimal',
		frontend_input	= 'text',
		is_required	= 0,
		is_user_defined	= 1,
		frontend_label	= 'Volume Weight';

select @attribute_id:=attribute_id from {$this->getTable('eav_attribute')} where attribute_code='volume_weight';

insert ignore into {$this->getTable('catalog_eav_attribute')}
	set attribute_id 	= @attribute_id,
		is_visible 	= 1,
		used_in_product_listing	= 1,
		is_filterable_in_search	= 1;

");

if(Mage::getConfig()->getNode('matrixdays/specialvars/volume') == 1) {
    $entityTypeId = $installer->getEntityTypeId('catalog_product');

    $attributeSetArr = $installer->getConnection()->fetchAll("SELECT attribute_set_id FROM {$this->getTable('eav_attribute_set')} WHERE entity_type_id={$entityTypeId}");

    $depth = $installer->getAttributeId($entityTypeId,'ship_depth');
    $width = $installer->getAttributeId($entityTypeId,'ship_width');
    $height = $installer->getAttributeId($entityTypeId,'ship_height');

    foreach( $attributeSetArr as $attr)
    {
    	$attributeSetId= $attr['attribute_set_id'];

    	$installer->addAttributeGroup($entityTypeId,$attributeSetId,'Shipping','99');

    	$attributeGroupId = $installer->getAttributeGroupId($entityTypeId,$attributeSetId,'Shipping');

    	$installer->addAttributeToGroup($entityTypeId,$attributeSetId,$attributeGroupId,$depth,'99');
    	$installer->addAttributeToGroup($entityTypeId,$attributeSetId,$attributeGroupId,$width,'99');
    	$installer->addAttributeToGroup($entityTypeId,$attributeSetId,$attributeGroupId,$height,'99');
    };
}

$installer->endSetup();