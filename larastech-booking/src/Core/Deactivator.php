<?php

namespace Larastech\Booking\Core;

/**
 * Deactivator class.
 *
 * Runs on plugin deactivation.
 */
class Deactivator {

	/**
	 * Run on deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
