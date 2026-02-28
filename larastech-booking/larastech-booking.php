<?php
/**
 * Plugin Name:       Larastech Booking
 * Plugin URI:        https://larastech.com/booking
 * Description:       Modern Frontend Service Scheduler. A production-ready, monetizable, freemium WordPress booking plugin.
 * Version:           1.0.0
 * Author:            Larastech
 * Author URI:        https://larastech.com/
 * License:           GPL-2.0+
 * Text Domain:       larastech-booking
 * Domain Path:       /languages
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define constants.
 */
define( 'LT_BOOKING_VERSION', '1.0.0' );
define( 'LT_BOOKING_PATH', plugin_dir_path( __FILE__ ) );
define( 'LT_BOOKING_URL', plugin_dir_url( __FILE__ ) );
define( 'LT_BOOKING_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoloader.
 */
require_once LT_BOOKING_PATH . 'src/Core/Autoloader.php';

use Larastech\Booking\Core\Autoloader;
use Larastech\Booking\Core\Plugin;

Autoloader::register();

/**
 * Activation & Deactivation hooks.
 */
register_activation_hook( __FILE__, [ 'Larastech\Booking\Core\Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Larastech\Booking\Core\Deactivator', 'deactivate' ] );

/**
 * Initialize the plugin.
 */
function lt_booking_init() {
	return Plugin::get_instance();
}

lt_booking_init();
