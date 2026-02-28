<?php

namespace Larastech\Booking\Core;

/**
 * Autoloader class.
 *
 * Implements PSR-4 style autoloading for the plugin's namespace.
 */
class Autoloader {

	/**
	 * Register the autoloader.
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	/**
	 * Autoload logic.
	 *
	 * @param string $class The fully-qualified class name.
	 * @return void
	 */
	public static function autoload( $class ) {
		$prefix = 'Larastech\\Booking\\';
		$base_dir = LT_BOOKING_PATH . 'src/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
