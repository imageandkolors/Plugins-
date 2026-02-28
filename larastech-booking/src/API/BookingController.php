<?php

namespace Larastech\Booking\API;

use WP_REST_Controller;
use WP_REST_Server;
use Larastech\Booking\Booking\BookingManager;
use Larastech\Booking\Booking\BookingRepository;
use Larastech\Booking\Booking\SlotGenerator;

/**
 * Booking REST API Controller.
 */
class BookingController extends WP_REST_Controller {

	protected $namespace = 'larastech-booking/v1';
	protected $rest_base = 'bookings';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => [ $this, 'create_item_permissions_check' ],
			],
		] );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/slots', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_slots' ],
				'permission_callback' => '__return_true', // Public can see slots.
			],
		] );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => [ $this, 'update_item_permissions_check' ],
			],
		] );
	}

	public function get_items_permissions_check( $request ) {
		return current_user_can( 'lt_manage_bookings' );
	}

	public function create_item_permissions_check( $request ) {
		return true; // Customers can create bookings.
	}

	public function get_item_permissions_check( $request ) {
		return current_user_can( 'lt_view_frontend_dash' );
	}

	public function update_item_permissions_check( $request ) {
		return current_user_can( 'lt_manage_bookings' );
	}

	/**
	 * Get slots for a service, staff, and date.
	 */
	public function get_slots( $request ) {
		$staff_id   = $request->get_param( 'staff_id' );
		$service_id = $request->get_param( 'service_id' );
		$date       = $request->get_param( 'date' );

		if ( ! $staff_id || ! $service_id || ! $date ) {
			return new \WP_Error( 'missing_params', 'Missing required parameters', [ 'status' => 400 ] );
		}

		$generator = new SlotGenerator();
		$slots = $generator->generate_slots( $staff_id, $service_id, $date );

		return rest_ensure_response( $slots );
	}

	/**
	 * Create a booking.
	 */
	public function create_item( $request ) {
		$manager = new BookingManager();
		$result  = $manager->create_booking( $request->get_params() );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( [
			'id'      => $result,
			'message' => 'Booking created successfully',
		] );
	}

	/**
	 * Get bookings list.
	 */
	public function get_items( $request ) {
		$repository = new BookingRepository();
		$bookings   = $repository->get_bookings_by_date(
			$request->get_param( 'start_date' ) ?: date( 'Y-m-d' ),
			$request->get_param( 'end_date' ) ?: date( 'Y-m-d', strtotime( '+30 days' ) )
		);

		return rest_ensure_response( $bookings );
	}

	/**
	 * Get a single booking.
	 */
	public function get_item( $request ) {
		$repository = new BookingRepository();
		$booking = $repository->find( $request['id'] );

		if ( ! $booking ) {
			return new \WP_Error( 'not_found', 'Booking not found', [ 'status' => 404 ] );
		}

		return rest_ensure_response( $booking );
	}

	/**
	 * Update a booking (e.g., status change).
	 */
	public function update_item( $request ) {
		$manager = new BookingManager();
		$status  = sanitize_text_field( $request->get_param( 'status' ) );
		$id      = (int) $request['id'];

		$success = $manager->update_status( $id, $status );

		if ( ! $success ) {
			return new \WP_Error( 'update_failed', 'Failed to update booking status', [ 'status' => 400 ] );
		}

		return rest_ensure_response( [ 'success' => true ] );
	}
}
