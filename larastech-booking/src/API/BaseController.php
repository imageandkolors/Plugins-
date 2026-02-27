<?php

namespace Larastech\Booking\API;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Base REST API Controller.
 */
class BaseController extends WP_REST_Controller {

	/**
	 * Namespace for the REST API.
	 *
	 * @var string
	 */
	protected $namespace = 'larastech-booking/v1';

	/**
	 * Register the routes.
	 */
	public static function register() {
		$instance = new self();
		add_action( 'rest_api_init', [ $instance, 'register_routes' ] );

		$booking_controller = new BookingController();
		add_action( 'rest_api_init', [ $booking_controller, 'register_routes' ] );

		$service_controller = new ServiceController();
		add_action( 'rest_api_init', [ $service_controller, 'register_routes' ] );

		$staff_controller = new StaffController();
		add_action( 'rest_api_init', [ $staff_controller, 'register_routes' ] );

		$availability_controller = new AvailabilityController();
		add_action( 'rest_api_init', [ $availability_controller, 'register_routes' ] );

		$customer_controller = new CustomerController();
		add_action( 'rest_api_init', [ $customer_controller, 'register_routes' ] );
	}

	/**
	 * Register routes for the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/settings', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_settings' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );
	}

	/**
	 * Check if the user has permission to access the endpoint.
	 */
	public function check_permission() {
		return current_user_can( 'lt_manage_settings' );
	}

	/**
	 * Placeholder for getting settings.
	 */
	public function get_settings( $request ) {
		return rest_ensure_response( [ 'success' => true, 'data' => [] ] );
	}
}
