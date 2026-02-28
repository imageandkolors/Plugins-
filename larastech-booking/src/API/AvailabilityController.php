<?php

namespace Larastech\Booking\API;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Availability REST API Controller.
 */
class AvailabilityController extends WP_REST_Controller {

	protected $namespace = 'larastech-booking/v1';
	protected $rest_base = 'availability';

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );
	}

	public function check_permission() {
		return current_user_can( 'lt_manage_staff' );
	}

	public function get_items( $request ) {
		global $wpdb;
		$staff_id = $request->get_param( 'staff_id' );
		$table = $wpdb->prefix . 'larastech_availability';

		if ( $staff_id ) {
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE staff_id = %d", $staff_id ) );
		} else {
			$results = $wpdb->get_results( "SELECT * FROM $table" );
		}

		return rest_ensure_response( $results );
	}

	public function create_item( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_availability';

		$data = [
			'staff_id'    => $request->get_param( 'staff_id' ),
			'day_of_week' => $request->get_param( 'day_of_week' ),
			'start_time'  => $request->get_param( 'start_time' ),
			'end_time'    => $request->get_param( 'end_time' ),
		];

		$wpdb->insert( $table, $data );
		return rest_ensure_response( [ 'id' => $wpdb->insert_id ] );
	}

	public function delete_item( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_availability';
		$wpdb->delete( $table, [ 'id' => $request['id'] ] );
		return rest_ensure_response( [ 'success' => true ] );
	}
}
