<?php
function oryx_uninstall()
{
	global $wpdb;
	
	// Delete options table
	$wpdb->query("DROP TABLE `oryx_options`");
	
	// Delete job list table
	$wpdb->query("DROP TABLE `oryx_joblist`");
	
	// Delete debug table
	$wpdb->query("DROP TABLE `oryx_debug`");
	
	// Delete tags table
	$wpdb->query("DROP TABLE `oryx_tags`");
	
	// Delete relationships table
	$wpdb->query("DROP TABLE `oryx_relationships`");
}