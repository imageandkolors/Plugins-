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
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
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

	public function create_item( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_staff';
		$wpdb->insert( $table, [
			'full_name' => sanitize_text_field( $request['full_name'] ),
			'email'     => sanitize_email( $request['email'] ),
			'phone'     => sanitize_text_field( $request['phone'] ),
			'status'    => sanitize_text_field( $request['status'] ?: 'active' ),
		] );
		return rest_ensure_response( [ 'id' => $wpdb->insert_id ] );
	}

	public function update_item( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_staff';
		$wpdb->update( $table, [
			'full_name' => sanitize_text_field( $request['full_name'] ),
			'email'     => sanitize_email( $request['email'] ),
			'phone'     => sanitize_text_field( $request['phone'] ),
			'status'    => sanitize_text_field( $request['status'] ),
		], [ 'id' => $request['id'] ] );
		return rest_ensure_response( [ 'success' => true ] );
	}

	public function delete_item( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_staff';
		$wpdb->delete( $table, [ 'id' => $request['id'] ] );
		return rest_ensure_response( [ 'success' => true ] );
	}

	public function get_items( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_staff';
		$results = $wpdb->get_results( "SELECT * FROM $table WHERE status = 'active'" );
		return rest_ensure_response( $results );
	}
}
