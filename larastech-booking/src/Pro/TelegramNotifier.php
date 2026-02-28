<?php

namespace Larastech\Booking\Pro;

use Larastech\Booking\Common\Logger;
use Larastech\Booking\Common\Settings;

/**
 * Telegram Notifier class.
 */
class TelegramNotifier {

	/**
	 * Send Telegram notification.
	 */
	public static function send_notification( $booking_id, $type ) {
		$bot_token = Settings::get( 'pro_telegram_token' );
		if ( ! $bot_token ) return;

		// Async-style: Use wp_schedule_single_event for non-blocking execution.
		wp_schedule_single_event( time(), 'lt_booking_telegram_notify', [ $booking_id, $type ] );

		Logger::log( "Telegram: Queued $type notification for booking $booking_id" );
	}

	/**
	 * Actual delivery logic.
	 */
	public static function deliver( $booking_id, $type ) {
		// wp_remote_get( ... );
	}
}
