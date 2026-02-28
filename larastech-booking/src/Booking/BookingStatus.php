<?php

namespace Larastech\Booking\Booking;

/**
 * Booking Status Enum-like class.
 */
class BookingStatus {
	const PENDING   = 'pending';
	const APPROVED  = 'approved';
	const REJECTED  = 'rejected';
	const CANCELLED = 'cancelled';
	const COMPLETED = 'completed';

	/**
	 * Get all statuses.
	 */
	public static function get_all() {
		return [
			self::PENDING,
			self::APPROVED,
			self::REJECTED,
			self::CANCELLED,
			self::COMPLETED,
		];
	}
}
