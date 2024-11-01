<?php
include_once('oryx-functions.php');

function oryx_install()
{
	global $wpdb;
		
	// Create debug table
	$debug =  "CREATE TABLE IF NOT EXISTS `oryx_debug` (
	`id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	`message` TEXT NOT NULL,  
	`timestamp` TIMESTAMP NOT NULL
	)";
	$wpdb->query($debug);
	
	oryx_debug("------------");
	oryx_debug("Installation started ".date('d/m/Y H:i:s'));
	
	// Create options table
	$options = "CREATE TABLE IF NOT EXISTS `oryx_options` (
	`id` INT(1) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`key` VARCHAR( 32 ) NOT NULL ,
	`value` TEXT NULL
	)";
	$wpdb->query($options);
	oryx_debug("Created oryx_debug");
	oryx_debug("Created oryx_options");
	
	// Insert default options values
	$insert = "INSERT IGNORE INTO `oryx_options` (
	`id`, `key`, `value`) VALUES 
	(NULL, 'oc-api-key', ''), 
	(NULL, 'execute', 'daily'), 
	(NULL, 'last-run', '-'), 
	(NULL, 'uninstall-tables', '-'), 
	(NULL, 'tag-relevance', '0'),
	(NULL, 'post-relevance', '0'),
	(NULL, 'use-oc', '0'),
	(NULL, 'num-to-show', 5), 
	(NULL, 'use-yahoo', '0'),
	(NULL, 'yahoo-api-key', '')
	";
	$wpdb->query($insert);
	oryx_debug("Inserted default values to oryx_options");
	
	// Create job list table
	$joblist =  "CREATE TABLE IF NOT EXISTS `oryx_joblist` (
	`id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	`blog_id` BIGINT( 20 ) NOT NULL, 
	`post_id` BIGINT( 20 ) NOT NULL, 
	`web_service` TINYINT(1) NOT NULL DEFAULT '0',
	`relationship` TINYINT(1) NOT NULL DEFAULT '0', 
	`timestamp` TIMESTAMP NOT NULL
	)";
	$wpdb->query($joblist);
	oryx_debug("Created oryx_joblist");
	
	// Create tags table
	$tags =  "CREATE TABLE IF NOT EXISTS `oryx_tags` (
	`id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	`tag` TEXT NOT NULL, 
	`blog_id` BIGINT( 20 ) NOT NULL, 
	`post_id` BIGINT( 20 ) NOT NULL, 
	`source` VARCHAR( 20 ) NOT NULL, 
	`relevance` INT( 3 ) NOT NULL DEFAULT '50', 
	`timestamp` TIMESTAMP NOT NULL
	)";
	$wpdb->query($tags);
	oryx_debug("Created oryx_tags");
	
	// Create relationships table
	$relationships =  "CREATE TABLE IF NOT EXISTS `oryx_relationships` (
  `id` int(20) NOT NULL auto_increment,
  `active_blog` int(20) NOT NULL,
  `active_post` int(20) NOT NULL,
  `passive_blog` int(20) NOT NULL,
  `passive_post` int(20) NOT NULL,
  `relevance` int(3) NOT NULL default '50',
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
)";
	$wpdb->query($relationships);
	oryx_debug("Created oryx_relationships");
	
	oryx_debug("Installation completed");
	oryx_debug("------------");
}

?>