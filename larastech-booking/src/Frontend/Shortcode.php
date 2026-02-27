<?php

namespace Larastech\Booking\Frontend;

/**
 * Shortcode class to render the booking app.
 */
class Shortcode {

	public static function init() {
		add_shortcode( 'lt_booking_app', [ __CLASS__, 'render_app' ] );
	}

	public static function render_app() {
		ob_start();
		include LT_BOOKING_PATH . 'templates/booking-app.php';
		return ob_get_clean();
	}
}
