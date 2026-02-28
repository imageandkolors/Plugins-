<?php

namespace Larastech\BookingPro\Integrations;

class TelegramNotifier {
	public static function send_notification( $booking_id, $type ) {
		wp_schedule_single_event( time(), 'lt_booking_telegram_notify', [ $booking_id, $type ] );
	}
	public static function deliver( $booking_id, $type ) {
		// Delivery logic.
	}
}
