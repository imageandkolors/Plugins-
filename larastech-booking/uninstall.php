<?php

/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Global $wpdb object.
 * @var wpdb $wpdb
 */
global $wpdb;

// 1. Delete plugin settings/options if any in wp_options.
delete_option( 'lt_booking_version' );

// 2. Drop custom tables.
$tables = [
	'services',
	'staff',
	'service_staff',
	'customers',
	'bookings',
	'availability',
	'exceptions',
	'payments',
	'notifications',
	'settings',
];

foreach ( $tables as $table ) {
	$table_name = $wpdb->prefix . 'larastech_' . $table;
	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}

// 3. Remove roles/capabilities.
remove_role( 'booking_manager' );

$admin = get_role( 'administrator' );
if ( $admin ) {
	$admin->remove_cap( 'lt_manage_bookings' );
	$admin->remove_cap( 'lt_manage_services' );
	$admin->remove_cap( 'lt_manage_staff' );
	$admin->remove_cap( 'lt_manage_settings' );
	$admin->remove_cap( 'lt_view_frontend_dash' );
}
