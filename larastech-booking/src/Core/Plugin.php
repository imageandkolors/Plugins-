<?php

namespace Larastech\Booking\Core;

/**
 * Singleton core loader.
 *
 * Manages plugin-wide hooks and components.
 */
class Plugin {

	/**
	 * The single instance of the class.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the class instance.
	 *
	 * @return Plugin
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
		$this->define_constants();
		$this->init();
	}

	/**
	 * Define plugin-wide constants.
	 */
	private function define_constants() {
		if ( ! defined( 'LT_BOOKING_PRO_ACTIVE' ) ) {
			define( 'LT_BOOKING_PRO_ACTIVE', class_exists( 'Larastech\BookingPro\Core\Plugin' ) );
		}
	}

	/**
	 * Initialize plugin components.
	 */
	private function init() {
		add_action( 'plugins_loaded', [ $this, 'load_modules' ] );
	}

	/**
	 * Load modular components.
	 */
	public function load_modules() {
		// Basic routing, API controllers, and hooks should go here.
		\Larastech\Booking\API\BaseController::register();
		\Larastech\Booking\Core\Roles::init();
		\Larastech\Booking\Handlers\AjaxHandler::init();
		\Larastech\Booking\Frontend\Assets::init();
		\Larastech\Booking\Frontend\Shortcode::init();
	}
}
