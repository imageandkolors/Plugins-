<?php

namespace Larastech\Booking\Booking;

use DateTime;

/**
 * Conflict Resolver class.
 */
class ConflictResolver {

	/**
	 * Check if a desired booking slot is still available.
	 */
	public function is_conflicting( $staff_id, $date, $start_time, $end_time ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_bookings';

		// Count bookings that overlap with the desired time.
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table
			 WHERE staff_id = %d
			 AND booking_date = %s
			 AND status NOT IN ('cancelled', 'rejected')
			 AND (
				 (start_time < %s AND end_time > %s)
			 )",
			$staff_id,
			$date,
			$end_time,
			$start_time
		) );

		return ( (int) $count > 0 );
	}
}
