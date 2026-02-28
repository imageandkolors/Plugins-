<?php
/**
 * Plugin Name:       Larastech Booking Pro
 * Plugin URI:        https://larastech.com/booking-pro
 * Description:       Pro extension for Larastech Booking. Unlocks WhatsApp, Telegram, and Google Sheets integrations.
 * Version:           1.0.0
 * Author:            Larastech
 * Author URI:        https://larastech.com/
 * License:           GPL-2.0+
 * Text Domain:       larastech-booking-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LT_BOOKING_PRO_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Pro Autoloader.
 */
spl_autoload_register( function ( $class ) {
	$prefix = 'Larastech\\BookingPro\\';
	$base_dir = LT_BOOKING_PRO_PATH . 'src/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

/**
 * Initialize Pro.
 */
function lt_booking_pro_init() {
	if ( class_exists( 'Larastech\BookingPro\Core\ProManager' ) ) {
		return \Larastech\BookingPro\Core\ProManager::get_instance();
	}
}

add_action( 'plugins_loaded', 'lt_booking_pro_init' );
