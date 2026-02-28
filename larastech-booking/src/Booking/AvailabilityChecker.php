<?php

namespace Larastech\Booking\Booking;

/**
 * Availability Checker class.
 */
class AvailabilityChecker {

	/**
	 * Get staff availability for a specific day.
	 */
	public function get_staff_availability( $staff_id, $day_of_week ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_availability';

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT start_time, end_time FROM $table WHERE staff_id = %d AND day_of_week = %d",
			$staff_id,
			$day_of_week
		) );
	}

	/**
	 * Get staff exceptions (holidays/overrides) for a specific date.
	 */
	public function get_staff_exceptions( $staff_id, $date ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_exceptions';

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT start_time, end_time, is_day_off FROM $table WHERE staff_id = %d AND exception_date = %s",
			$staff_id,
			$date
		) );
	}
}
