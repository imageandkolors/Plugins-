<?php

namespace Larastech\Booking\API;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Service REST API Controller.
 */
class ServiceController extends WP_REST_Controller {

	protected $namespace = 'larastech-booking/v1';
	protected $rest_base = 'services';

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
		return current_user_can( 'lt_manage_services' );
	}

	public function create_item( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_services';
		$wpdb->insert( $table, [
			'title'       => sanitize_text_field( $request['title'] ),
			'description' => sanitize_textarea_field( $request['description'] ),
			'duration'    => absint( $request['duration'] ),
			'price'       => floatval( $request['price'] ),
			'status'      => sanitize_text_field( $request['status'] ?: 'active' ),
		] );
		return rest_ensure_response( [ 'id' => $wpdb->insert_id ] );
	}

	public function update_item( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_services';
		$wpdb->update( $table, [
			'title'       => sanitize_text_field( $request['title'] ),
			'description' => sanitize_textarea_field( $request['description'] ),
			'duration'    => absint( $request['duration'] ),
			'price'       => floatval( $request['price'] ),
			'status'      => sanitize_text_field( $request['status'] ),
		], [ 'id' => $request['id'] ] );
		return rest_ensure_response( [ 'success' => true ] );
	}

	public function delete_item( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_services';
		$wpdb->delete( $table, [ 'id' => $request['id'] ] );
		return rest_ensure_response( [ 'success' => true ] );
	}

	public function get_items( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_services';
		$results = $wpdb->get_results( "SELECT * FROM $table WHERE status = 'active'" );
		return rest_ensure_response( $results );
	}
}
