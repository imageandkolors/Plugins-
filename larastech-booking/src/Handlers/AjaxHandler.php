<?php

namespace Larastech\Booking\Handlers;

/**
 * Secure AJAX handler class.
 */
class AjaxHandler {

	/**
	 * Register AJAX hooks.
	 */
	public static function init() {
		$instance = new self();
		add_action( 'wp_ajax_lt_booking_action', [ $instance, 'handle_ajax' ] );
		// For public actions if needed:
		// add_action( 'wp_ajax_nopriv_lt_booking_action', [ $instance, 'handle_ajax' ] );
	}

	/**
	 * Handle AJAX request.
	 */
	public function handle_ajax() {
		check_ajax_referer( 'lt_booking_nonce', 'nonce' );

		$action = sanitize_text_field( $_POST['sub_action'] ?? '' );

		switch ( $action ) {
			case 'create_booking':
				$this->create_booking();
				break;
			case 'get_profile':
				if ( ! current_user_can( 'lt_view_frontend_dash' ) ) {
					wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
				}
				$this->get_profile();
				break;
			default:
				wp_send_json_error( [ 'message' => 'Invalid action' ] );
		}
	}

	/**
	 * Create booking via AJAX.
	 */
	private function create_booking() {
		$manager = new \Larastech\Booking\Booking\BookingManager();
		$result  = $manager->create_booking( $_POST );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [
			'id'      => $result,
			'message' => 'Booking created successfully',
		] );
	}

	/**
	 * Example sub-action.
	 */
	private function get_profile() {
		wp_send_json_success( [ 'user' => wp_get_current_user()->display_name ] );
	}
}
