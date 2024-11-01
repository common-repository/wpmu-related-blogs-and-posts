<?php
/***************************************************************************
Plugin Name: WPMU Contextual Related Posts
Plugin URI: http://code.google.com/p/jiscpress
Description: Creates relationships between WPMU blog posts based on user defined tags, relevant Open Calais tags and extracted terms
Version: 1.1
Author: Alex Bilbie
Author URI: http://www.alexbilbie.com/
****************************************************************************

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are
met:

1. Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright
notice, this list of conditions and the following disclaimer in the
documentation and/or other materials provided with the distribution.
3. The name of the author may not be used to endorse or promote
products derived from this software without specific prior written
permission.

THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.

http://www.xfree86.org/3.3.6/COPYRIGHT2.html#5

****************************************************************************

This plugin was created for the JISC (www.jisc.ac.uk) funded JISCPress project (see http://jiscpress.blogs.lincoln.ac.uk/)

***************************************************************************/

// Load the functions file
require_once('oryx-functions.php');


/***************************************************************************
	
	Install the plugin
	
***************************************************************************/

function hook_install_oryx()
{
	require_once('oryx-install.php');
	oryx_install();
	
	// Install cron jobs
	if (!wp_next_scheduled('hook_oryx_cron_joblist')) {
		wp_schedule_event(time(), 'daily', 'hook_oryx_cron_opencalais');
	}
	
	if (!wp_next_scheduled('hook_oryx_cron_relationships')) {
		wp_schedule_event(time(), 'daily', 'hook_oryx_cron_relationships');
	}
}

register_activation_hook(__FILE__, 'hook_install_oryx');


/***************************************************************************
	
	Unistall the plugin
	
***************************************************************************/

function hook_uninstall_oryx()
{
	require_once('oryx-uninstall.php');
	oryx_uninstall();
	
	// Unistall cron jobs
	wp_clear_scheduled_hook('hook_oryx_cron_opencalais');
	wp_clear_scheduled_hook('hook_oryx_cron_relationships');
}

register_deactivation_hook(__FILE__, 'hook_uninstall_oryx');


/***************************************************************************
	
	Plugin initilization actions
	
***************************************************************************/


function oryx_init()
{
	// Add options page to the WPMU admin menu
	add_submenu_page('wpmu-admin.php', 'WMPU Related Posts Administration','WPMU Related Posts', 'manage_options', 'oryx-display.php', 'hook_oryx_display');
}

// Widget logic
function hook_oryx_widget()
{
	require_once('oryx-widget.php');
	oryx_widget_display();
}

// Widget admin logic
function hook_oryx_widget_admin()
{
	require_once('oryx-widget.php');
	oryx_widget_control_display();
}

// Runs the cron job to throw the content at Open Calais + Yahoo
function fire_oryx_cron_throw()
{
	require_once('oryx-cron.php');
	oryx_cron_throw();
}

// Runs the cron job to establish relationships between blogs + posts
function fire_oryx_cron_relationships()
{
	require_once('oryx-cron.php');
	oryx_cron_relationships();
}


// Display the admin page
function hook_oryx_display()
{
	include_once('oryx-display.php');
	oryx_display();
}

// Add menus
add_action('admin_menu', 'oryx_init');

// Add new/updated blog posts to the joblist
add_action('publish_post', 'oryx_jobs_add');

// When a post is deleted remove it's relationships so that no errors occur
add_action('deleted_post', 'oryx_relationship_delete');

// Define the cron functions
add_action('hook_oryx_cron_joblist', 'fire_oryx_cron_throw');
add_action('hook_oryx_cron_relationships', 'fire_oryx_cron_relationships');

// Register widget
register_sidebar_widget('WPMU Related Posts', 'hook_oryx_widget');
register_widget_control('WPMU Related Posts', 'hook_oryx_widget_admin');
?>