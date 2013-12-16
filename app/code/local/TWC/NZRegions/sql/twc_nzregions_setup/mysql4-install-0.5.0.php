<?php

$installer = $this;
$installer->startSetup();
$installer->run("
DELETE FROM `{$this->getTable('directory_country_region')}` WHERE country_id = 'NZ';

INSERT INTO `{$this->getTable('directory_country_region')}` (`country_id`, `code`, `default_name`) VALUES ('NZ', 'NTH', 'North Island');
INSERT INTO `{$this->getTable('directory_country_region_name')}` (`locale`, `region_id`, `name`) VALUES
('en_US', LAST_INSERT_ID(), 'North Island'), ('en_NZ', LAST_INSERT_ID(), 'North Island');

INSERT INTO `{$this->getTable('directory_country_region')}` (`country_id`, `code`, `default_name`) VALUES ('NZ', 'STH', 'South Island');
INSERT INTO `{$this->getTable('directory_country_region_name')}` (`locale`, `region_id`, `name`) VALUES
('en_US', LAST_INSERT_ID(), 'South Island'), ('en_NZ', LAST_INSERT_ID(), 'South Island');
");
//$installer->installEntities();
$installer->endSetup();
