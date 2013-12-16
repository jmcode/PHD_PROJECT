<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD dispatch_date varchar(30);
ALTER IGNORE TABLE {$this->getTable('sales_flat_order')}  ADD expected_delivery varchar(30);

    ");

$installer->endSetup();