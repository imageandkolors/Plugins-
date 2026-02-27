<?php

namespace Larastech\Booking\API;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Staff REST API Controller.
 */
class StaffController extends WP_REST_Controller {

	protected $namespace = 'larastech-booking/v1';
	protected $rest_base = 'staff';

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => '__return_true',
			],
		] );
	}

	public function get_items( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_staff';
		$results = $wpdb->get_results( "SELECT * FROM $table WHERE status = 'active'" );
		return rest_ensure_response( $results );
	}
}
