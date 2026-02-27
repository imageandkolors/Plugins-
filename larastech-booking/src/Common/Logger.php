<?php

namespace Larastech\Booking\Common;

/**
 * Logger utility class.
 */
class Logger {

	/**
	 * Log a message to the debug log.
	 *
	 * @param mixed $message The message to log.
	 */
	public static function log( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}
}
