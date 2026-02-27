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
	 * Check if Pro is active.
	 */
	public function is_pro() {
		return defined( 'LT_BOOKING_PRO_ACTIVE' ) && LT_BOOKING_PRO_ACTIVE;
	}

	/**
	 * Initialize Pro features.
	 */
	private function init_pro_features() {
		ProHooks::init();
	}
}
