<?php

namespace Larastech\BookingPro\Core;

/**
 * Pro Manager class.
 */
class ProManager {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init_pro_features();
	}

	private function init_pro_features() {
		// Initialization logic for Pro hooks and features.
		\Larastech\BookingPro\Core\ProHooks::init();
	}
}
