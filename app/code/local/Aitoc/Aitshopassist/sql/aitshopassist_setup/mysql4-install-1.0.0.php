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
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('aitoc_aitshopassist_page')};
CREATE TABLE {$this->getTable('aitoc_aitshopassist_page')} (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `status` int(10) NOT NULL,
  `show_in_bar` int(10) NOT NULL,
  PRIMARY KEY  (`entity_id`))
ENGINE=InnoDB CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('aitoc_aitshopassist_page_category')};
CREATE TABLE {$this->getTable('aitoc_aitshopassist_page_category')} (
  `page_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
   KEY `aitoc_aitshopassist_page_category_page` (`page_id`),
   KEY `aitoc_aitshopassist_page_category_category` (`category_id`)
) ENGINE=InnoDB CHARSET=utf8;

ALTER TABLE {$this->getTable('aitoc_aitshopassist_page_category')}
  ADD CONSTRAINT `aitoc_aitshopassist_page_category_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `".$installer->getTable('catalog/category')."` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aitoc_aitshopassist_page_category_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `".$installer->getTable('aitoc_aitshopassist_page')."` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- DROP TABLE IF EXISTS {$this->getTable('aitoc_aitshopassist_question')};
CREATE TABLE {$this->getTable('aitoc_aitshopassist_question')} (
    `entity_id` int(10) unsigned NOT NULL auto_increment,  
    `page_id` int(10) unsigned NOT NULL,
    `position` int(10) NOT NULL,
  PRIMARY KEY  (`entity_id`),
  KEY `aitoc_aitshopassist_question_page` (`page_id`)
) ENGINE=InnoDB CHARSET=utf8;

ALTER TABLE {$this->getTable('aitoc_aitshopassist_question')}
  ADD CONSTRAINT `aitoc_aitshopassist_question_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `".$installer->getTable('aitoc_aitshopassist_page')."` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- DROP TABLE IF EXISTS {$this->getTable('aitoc_aitshopassist_answer')};
CREATE TABLE {$this->getTable('aitoc_aitshopassist_answer')} (
    `entity_id` int(10) unsigned NOT NULL auto_increment,  
    `question_id` int(10) unsigned NOT NULL,
    `condition` text default '',
    `position` int(10) NOT NULL,
  PRIMARY KEY  (`entity_id`),
  KEY `aitoc_aitshopassist_answer_question` (`question_id`)
) ENGINE=InnoDB CHARSET=utf8;

ALTER TABLE {$this->getTable('aitoc_aitshopassist_answer')}
  ADD CONSTRAINT `aitoc_aitshopassist_page_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `".$installer->getTable('aitoc_aitshopassist_question')."` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- DROP TABLE IF EXISTS {$this->getTable('aitoc_aitshopassist_question_dependence')};
CREATE TABLE {$this->getTable('aitoc_aitshopassist_question_dependence')} (
    `entity_id` int(10) unsigned NOT NULL auto_increment,  
    `dependence_question_id` int(10) unsigned NOT NULL,
    `dependence_answer_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`entity_id`),
  KEY `aitoc_aitshopassist_question_dependence_answer` (`dependence_answer_id`),
  KEY `aitoc_aitshopassist_question_dependence_dependence_question` (`dependence_question_id`)
) ENGINE=InnoDB CHARSET=utf8;

ALTER TABLE {$this->getTable('aitoc_aitshopassist_question_dependence')}
  ADD CONSTRAINT `aitoc_aitshopassist_question_dependence_ibfk_1` FOREIGN KEY (`dependence_answer_id`) REFERENCES `".$installer->getTable('aitoc_aitshopassist_answer')."` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aitoc_aitshopassist_question_dependence_ibfk_2` FOREIGN KEY (`dependence_question_id`) REFERENCES `".$installer->getTable('aitoc_aitshopassist_question')."` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- DROP TABLE IF EXISTS {$this->getTable('aitoc_aitshopassist_page_text')};
CREATE TABLE {$this->getTable('aitoc_aitshopassist_page_text')} (
    `store_id` smallint(5) unsigned NOT NULL,
    `page_id` int(10) unsigned NOT NULL,
    `text` text default '',
    `field` varchar(21) default '',

  KEY `aitoc_aitshopassist_page_text_entity` (`page_id`),
  KEY `aitoc_aitshopassist_page_text_store` (`store_id`)
) ENGINE=InnoDB CHARSET=utf8;

ALTER TABLE {$this->getTable('aitoc_aitshopassist_page_text')}
ADD CONSTRAINT  `aitoc_aitshopassist_page_text_store_ibfk_1` FOREIGN KEY (`store_id` ) REFERENCES `".$installer->getTable('core_store')."` (  `store_id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
ADD CONSTRAINT  `aitoc_aitshopassist_page_text_page_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `".$installer->getTable('aitoc_aitshopassist_page')."` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- DROP TABLE IF EXISTS {$this->getTable('aitoc_aitshopassist_question_text')};
CREATE TABLE {$this->getTable('aitoc_aitshopassist_question_text')} (
    `store_id` smallint(5) unsigned NOT NULL,
    `question_id` int(10) unsigned NOT NULL,
    `text` text default '',
    `field` varchar(21) default '',

  KEY `aitoc_aitshopassist_page_text_entity` (`question_id`),
  KEY `aitoc_aitshopassist_page_text_store` (`store_id`)
) ENGINE=InnoDB CHARSET=utf8;

ALTER TABLE {$this->getTable('aitoc_aitshopassist_question_text')}
ADD CONSTRAINT  `aitoc_aitshopassist_question_text_store_ibfk_1` FOREIGN KEY (`store_id` ) REFERENCES `".$installer->getTable('core_store')."` (  `store_id` ) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT  `aitoc_aitshopassist_question_text_question_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `".$installer->getTable('aitoc_aitshopassist_question')."` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- DROP TABLE IF EXISTS {$this->getTable('aitoc_aitshopassist_answer_text')};
CREATE TABLE {$this->getTable('aitoc_aitshopassist_answer_text')} (
    `store_id` smallint(5) unsigned NOT NULL,
    `answer_id` int(10) unsigned NOT NULL,
    `text` text default '',

  KEY `aitoc_aitshopassist_page_text_entity` (`answer_id`),
  KEY `aitoc_aitshopassist_page_text_store` (`store_id`)
) ENGINE=InnoDB CHARSET=utf8;

ALTER TABLE {$this->getTable('aitoc_aitshopassist_answer_text')}
ADD CONSTRAINT  `aitoc_aitshopassist_answer_text_store_ibfk_1` FOREIGN KEY (`store_id` ) REFERENCES  `".$installer->getTable('core_store')."` (  `store_id` ) ON DELETE CASCADE ON UPDATE CASCADE ,
ADD CONSTRAINT  `aitoc_aitshopassist_answer_text_answer_ibfk_2` FOREIGN KEY (`answer_id`) REFERENCES `".$installer->getTable('aitoc_aitshopassist_answer')."` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

");

$installer->endSetup();