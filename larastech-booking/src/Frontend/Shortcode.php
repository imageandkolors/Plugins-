<?php

namespace Larastech\Booking\Frontend;

/**
 * Shortcode class to render the booking app.
 */
class Shortcode {

	public static function init() {
		add_shortcode( 'lt_booking_app', [ __CLASS__, 'render_app' ] );
		add_shortcode( 'lt_admin_app', [ __CLASS__, 'render_admin_app' ] );
	}

	public static function render_app() {
		ob_start();
		include LT_BOOKING_PATH . 'templates/booking-app.php';
		return ob_get_clean();
	}

	public static function render_admin_app() {
		if ( ! current_user_can( 'lt_view_frontend_dash' ) ) {
			return __( 'You do not have permission to view this page.', 'larastech-booking' );
		}
		ob_start();
		include LT_BOOKING_PATH . 'templates/admin-app.php';
		return ob_get_clean();
	}
}
