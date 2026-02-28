<?php

namespace Larastech\Booking\Database;

/**
 * Database Schema class.
 *
 * Handles creation of custom database tables.
 */
class Schema {

	/**
	 * Create or update custom database tables.
	 *
	 * @return void
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'larastech_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = [];

		// Services Table.
		$sql[] = "CREATE TABLE {$prefix}services (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			description text DEFAULT NULL,
			duration int(11) unsigned NOT NULL DEFAULT 30,
			price decimal(10,2) NOT NULL DEFAULT 0.00,
			status varchar(50) NOT NULL DEFAULT 'active',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;";

		// Staff Table.
		$sql[] = "CREATE TABLE {$prefix}staff (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned DEFAULT NULL,
			full_name varchar(255) NOT NULL,
			email varchar(255) NOT NULL,
			phone varchar(50) DEFAULT NULL,
			status varchar(50) NOT NULL DEFAULT 'active',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id  (user_id)
		) $charset_collate;";

		// Service Staff Pivot Table.
		$sql[] = "CREATE TABLE {$prefix}service_staff (
			service_id bigint(20) unsigned NOT NULL,
			staff_id bigint(20) unsigned NOT NULL,
			PRIMARY KEY  (service_id, staff_id),
			KEY staff_id  (staff_id)
		) $charset_collate;";

		// Customers Table.
		$sql[] = "CREATE TABLE {$prefix}customers (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned DEFAULT NULL,
			full_name varchar(255) NOT NULL,
			email varchar(255) NOT NULL,
			phone varchar(50) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id  (user_id),
			UNIQUE KEY email  (email)
		) $charset_collate;";

		// Bookings Table.
		$sql[] = "CREATE TABLE {$prefix}bookings (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			service_id bigint(20) unsigned NOT NULL,
			staff_id bigint(20) unsigned NOT NULL,
			customer_id bigint(20) unsigned NOT NULL,
			booking_date date NOT NULL,
			start_time time NOT NULL,
			end_time time NOT NULL,
			status varchar(50) NOT NULL DEFAULT 'pending',
			total_price decimal(10,2) NOT NULL DEFAULT 0.00,
			notes text DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY service_id  (service_id),
			KEY staff_id  (staff_id),
			KEY customer_id  (customer_id),
			KEY booking_date  (booking_date),
			KEY status  (status)
		) $charset_collate;";

		// Availability Table.
		$sql[] = "CREATE TABLE {$prefix}availability (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			staff_id bigint(20) unsigned NOT NULL,
			day_of_week tinyint(3) unsigned NOT NULL,
			start_time time NOT NULL,
			end_time time NOT NULL,
			PRIMARY KEY  (id),
			KEY staff_id  (staff_id)
		) $charset_collate;";

		// Exceptions Table.
		$sql[] = "CREATE TABLE {$prefix}exceptions (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			staff_id bigint(20) unsigned NOT NULL,
			exception_date date NOT NULL,
			start_time time DEFAULT NULL,
			end_time time DEFAULT NULL,
			is_day_off tinyint(1) NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY staff_id  (staff_id),
			KEY exception_date  (exception_date)
		) $charset_collate;";

		// Payments Table.
		$sql[] = "CREATE TABLE {$prefix}payments (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			booking_id bigint(20) unsigned NOT NULL,
			transaction_id varchar(255) NOT NULL,
			gateway varchar(50) NOT NULL,
			amount decimal(10,2) NOT NULL,
			currency varchar(10) NOT NULL DEFAULT 'USD',
			status varchar(50) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY booking_id  (booking_id)
		) $charset_collate;";

		// Notifications Table.
		$sql[] = "CREATE TABLE {$prefix}notifications (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			booking_id bigint(20) unsigned NOT NULL,
			type varchar(50) NOT NULL,
			status varchar(50) NOT NULL,
			sent_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY booking_id  (booking_id)
		) $charset_collate;";

		// Settings Table.
		$sql[] = "CREATE TABLE {$prefix}settings (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			option_name varchar(255) NOT NULL,
			option_value longtext DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY option_name  (option_name)
		) $charset_collate;";

		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}
}
