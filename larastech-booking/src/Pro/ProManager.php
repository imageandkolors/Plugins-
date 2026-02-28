<?php

namespace Larastech\Booking\Pro;

/**
 * Pro Manager class.
 *
 * Detects Pro status and manages Pro features.
 */
class ProManager {

	/**
	 * Instance.
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		if ( $this->is_pro() ) {
			$this->init_pro_features();
		}
	}

	/**
	 * Check if Pro is active and licensed.
	 */
	public function is_pro() {
		$pro_active = defined( 'LT_BOOKING_PRO_ACTIVE' ) && LT_BOOKING_PRO_ACTIVE;
		if ( ! $pro_active ) {
			return false;
		}
		return LicenseManager::is_active();
	}

	/**
	 * Initialize Pro features.
	 */
	private function init_pro_features() {
		ProHooks::init();
	}
}
