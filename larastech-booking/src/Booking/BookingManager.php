<?php

namespace Larastech\Booking\Booking;

use Larastech\Booking\Common\Logger;

/**
 * Booking Manager class.
 */
class BookingManager {

	protected $repository;
	protected $resolver;

	public function __construct() {
		$this->repository = new BookingRepository();
		$this->resolver   = new ConflictResolver();
	}

	/**
	 * Create a new booking.
	 */
	public function create_booking( $data ) {
		// 1. Validate data.
		if ( empty( $data['service_id'] ) || empty( $data['staff_id'] ) || empty( $data['booking_date'] ) || empty( $data['start_time'] ) ) {
			return new \WP_Error( 'missing_data', __( 'Required booking data is missing.', 'larastech-booking' ) );
		}

		// Handle Customer.
		$customer_id = $data['customer_id'] ?? 0;
		if ( ! $customer_id && ! empty( $data['customer_email'] ) ) {
			$customer_id = $this->get_or_create_customer( $data );
		}

		if ( ! $customer_id ) {
			return new \WP_Error( 'missing_customer', __( 'Customer information is missing.', 'larastech-booking' ) );
		}

		// Calculate end time based on service duration.
		global $wpdb;
		$service = $wpdb->get_row( $wpdb->prepare(
			"SELECT duration, price FROM {$wpdb->prefix}larastech_services WHERE id = %d",
			$data['service_id']
		) );

		if ( ! $service ) {
			return new \WP_Error( 'invalid_service', __( 'Service not found.', 'larastech-booking' ) );
		}

		$start_time = $data['start_time'];
		$duration   = (int) $service->duration;
		$end_time   = date( 'H:i:s', strtotime( "$start_time +$duration minutes" ) );

		// 2. Check for conflicts.
		if ( $this->resolver->is_conflicting( $data['staff_id'], $data['booking_date'], $start_time, $end_time ) ) {
			Logger::log( "Booking conflict detected for staff {$data['staff_id']} on {$data['booking_date']} at $start_time" );
			return new \WP_Error( 'conflict', __( 'The selected slot is no longer available.', 'larastech-booking' ) );
		}

		// 3. Prepare data for DB.
		$booking_data = [
			'service_id'   => $data['service_id'],
			'staff_id'     => $data['staff_id'],
			'customer_id'  => $customer_id,
			'booking_date' => $data['booking_date'],
			'start_time'   => $start_time,
			'end_time'     => $end_time,
			'status'       => BookingStatus::PENDING,
			'total_price'  => $service->price,
			'notes'        => sanitize_textarea_field( $data['notes'] ?? '' ),
		];

		// 4. Insert into DB.
		$inserted = $this->repository->insert( $booking_data );

		if ( ! $inserted ) {
			return new \WP_Error( 'db_error', __( 'Failed to save booking.', 'larastech-booking' ) );
		}

		$booking_id = $wpdb->insert_id;

		/**
		 * Hook: booking_created.
		 */
		do_action( 'lt_booking_created', $booking_id, $booking_data );

		Logger::log( "Booking created successfully: ID $booking_id" );

		return $booking_id;
	}

	/**
	 * Get or create a customer by email.
	 */
	private function get_or_create_customer( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_customers';

		$email = sanitize_email( $data['customer_email'] );
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE email = %s", $email ) );

		if ( ! $id ) {
			$wpdb->insert( $table, [
				'full_name' => sanitize_text_field( $data['customer_name'] ?? '' ),
				'email'     => $email,
				'phone'     => sanitize_text_field( $data['customer_phone'] ?? '' ),
			] );
			$id = $wpdb->insert_id;
		}

		return $id;
	}

	/**
	 * Update booking status.
	 */
	public function update_status( $booking_id, $status ) {
		if ( ! in_array( $status, BookingStatus::get_all() ) ) {
			return false;
		}

		$updated = $this->repository->update( $booking_id, [ 'status' => $status ] );

		if ( $updated ) {
			do_action( 'lt_booking_updated', $booking_id, $status );
		}

		return $updated;
	}
}
