<?php

namespace Larastech\Booking\API;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Customer REST API Controller.
 */
class CustomerController extends WP_REST_Controller {

	protected $namespace = 'larastech-booking/v1';
	protected $rest_base = 'customers';

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );
	}

	public function check_permission() {
		return current_user_can( 'lt_manage_bookings' );
	}

	public function get_items( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_customers';
		$results = $wpdb->get_results( "SELECT * FROM $table" );
		return rest_ensure_response( $results );
	}
}
