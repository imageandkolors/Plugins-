<?php

namespace Larastech\Booking\Core;

use Larastech\Booking\Database\Schema;

/**
 * Activator class.
 *
 * Runs on plugin activation.
 */
class Activator {

	/**
	 * Run on activation.
	 *
	 * @return void
	 */
	public static function activate() {
		// Initialize database schema.
		Schema::create_tables();

		// Create roles and caps.
		Roles::register();

		// Flush rewrite rules for REST API.
		flush_rewrite_rules();
	}
}
