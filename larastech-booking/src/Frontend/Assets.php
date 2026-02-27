<?php

namespace Larastech\Booking\Frontend;

/**
 * Frontend Assets class.
 */
class Assets {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 */
	public static function enqueue_assets() {
		// Enqueue Tailwind via CDN for development (in production, this should be compiled).
		wp_enqueue_style( 'lt-booking-tailwind', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' );

		// Enqueue Alpine.js.
		wp_enqueue_script( 'lt-booking-alpine', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', [], '3.0.0', true );

		// Enqueue main app script.
		wp_enqueue_script( 'lt-booking-app', LT_BOOKING_URL . 'assets/js/booking-app.js', [ 'jquery' ], LT_BOOKING_VERSION, true );

		// Localize script for API and nonces.
		wp_localize_script( 'lt-booking-app', 'ltBookingData', [
			'rest_url' => esc_url_raw( rest_url( 'larastech-booking/v1' ) ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'lt_nonce' => wp_create_nonce( 'lt_booking_nonce' ),
		] );
	}
}
