<?php

namespace Larastech\Booking\Booking;

use DateTime;
use DateInterval;

/**
 * Slot Generator class.
 */
class SlotGenerator {

	protected $availability_checker;
	protected $booking_repository;

	public function __construct() {
		$this->availability_checker = new AvailabilityChecker();
		$this->booking_repository    = new BookingRepository();
	}

	/**
	 * Generate available slots for a staff member on a specific date for a service.
	 */
	public function generate_slots( $staff_id, $service_id, $date ) {
		$cache_key = "lt_slots_{$staff_id}_{$service_id}_{$date}";
		$cached_slots = get_transient( $cache_key );

		if ( false !== $cached_slots ) {
			return $cached_slots;
		}

		// 1. Get service duration.
		global $wpdb;
		$service = $wpdb->get_row( $wpdb->prepare(
			"SELECT duration FROM {$wpdb->prefix}larastech_services WHERE id = %d",
			$service_id
		) );

		if ( ! $service ) return [];
		$duration = (int) $service->duration;

		// 2. Get availability and exceptions.
		$day_of_week = (int) date( 'N', strtotime( $date ) ); // 1 (Mon) to 7 (Sun)
		$availability = $this->availability_checker->get_staff_availability( $staff_id, $day_of_week );
		$exceptions   = $this->availability_checker->get_staff_exceptions( $staff_id, $date );

		// Check if it's a day off.
		foreach ( $exceptions as $exception ) {
			if ( $exception->is_day_off ) {
				return [];
			}
		}

		// 3. Get existing bookings for this day.
		$bookings = $this->booking_repository->get_bookings_by_date( $date, $date, $staff_id );

		$slots = [];

		// If exceptions exist for hours, use them instead of regular availability?
		// For simplicity, let's assume exceptions override for the whole day or specific hours.
		$work_hours = ! empty( $exceptions ) ? $exceptions : $availability;

		foreach ( $work_hours as $hours ) {
			$start = new DateTime( $date . ' ' . $hours->start_time );
			$end   = new DateTime( $date . ' ' . $hours->end_time );

			$current = clone $start;
			while ( $current < $end ) {
				$slot_start = clone $current;
				$slot_end   = (clone $current)->add( new DateInterval( 'PT' . $duration . 'M' ) );

				if ( $slot_end > $end ) break;

				if ( $this->is_slot_available( $slot_start, $slot_end, $bookings ) ) {
					$slots[] = [
						'start' => $slot_start->format( 'H:i' ),
						'end'   => $slot_end->format( 'H:i' ),
					];
				}

				// Increment by duration or a fixed interval (e.g. 15 or 30 mins)
				$current->add( new DateInterval( 'PT' . $duration . 'M' ) );
			}
		}

		set_transient( $cache_key, $slots, HOUR_IN_SECONDS );

		return $slots;
	}

	/**
	 * Check if a slot overlaps with any existing bookings.
	 */
	private function is_slot_available( $start, $end, $bookings ) {
		foreach ( $bookings as $booking ) {
			$b_start = new DateTime( $booking->booking_date . ' ' . $booking->start_time );
			$b_end   = new DateTime( $booking->booking_date . ' ' . $booking->end_time );

			// Check for overlap.
			if ( $start < $b_end && $end > $b_start ) {
				return false;
			}
		}
		return true;
	}
}
