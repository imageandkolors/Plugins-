<?php

namespace Larastech\Booking\API;

use WP_REST_Controller;
use WP_REST_Server;
use Larastech\Booking\Pro\LicenseManager;

/**
 * License REST API Controller.
 */
class LicenseController extends WP_REST_Controller {

	protected $namespace = 'larastech-booking/v1';
	protected $rest_base = 'license';

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/activate', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'activate_license' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/status', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_status' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/deactivate', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'deactivate_license' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );
	}

	public function check_permission() {
		return current_user_can( 'lt_manage_settings' );
	}

	public function activate_license( $request ) {
		$key = sanitize_text_field( $request->get_param( 'key' ) );
		$success = LicenseManager::activate( $key );

		if ( ! $success ) {
			return new \WP_Error( 'invalid_key', 'Invalid license key', [ 'status' => 400 ] );
		}

		return rest_ensure_response( [ 'success' => true, 'status' => 'active' ] );
	}

	public function deactivate_license( $request ) {
		LicenseManager::deactivate();
		return rest_ensure_response( [ 'success' => true ] );
	}

	public function get_status( $request ) {
		return rest_ensure_response( [
			'is_active' => LicenseManager::is_active(),
			'license'   => get_option( LicenseManager::LICENSE_OPTION ),
		] );
	}
}
