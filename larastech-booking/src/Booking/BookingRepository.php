<?php

namespace Larastech\Booking\Booking;

use Larastech\Booking\Database\Repository;

/**
 * Booking Repository class.
 */
class BookingRepository extends Repository {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'bookings' );
	}

	/**
	 * Get bookings by date range.
	 */
	public function get_bookings_by_date( $start_date, $end_date, $staff_id = null ) {
		global $wpdb;

		$query = "SELECT * FROM {$this->table_name} WHERE booking_date BETWEEN %s AND %s";
		$params = [ $start_date, $end_date ];

		if ( $staff_id ) {
			$query .= " AND staff_id = %d";
			$params[] = $staff_id;
		}

		$query .= " ORDER BY booking_date ASC, start_time ASC";

		return $wpdb->get_results( $wpdb->prepare( $query, ...$params ) );
	}

	/**
	 * Get bookings for a specific customer.
	 */
	public function get_customer_bookings( $customer_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE customer_id = %d ORDER BY created_at DESC",
			$customer_id
		) );
	}
}
