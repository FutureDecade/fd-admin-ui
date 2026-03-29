<?php
/**
 * Uninstall FD Admin UI
 *
 * Fired when the plugin is deleted via the WordPress admin.
 * Cleans up all plugin data from the database.
 *
 * @package FD_Admin_UI
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'fd_admin_ui_options' );
delete_option( 'fd_admin_menu_order' );
delete_option( 'fd_admin_submenu_order' );
delete_option( 'fd_admin_custom_separators' );
