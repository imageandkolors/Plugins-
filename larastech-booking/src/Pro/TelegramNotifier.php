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

		// Placeholder for Telegram Bot API call.
		Logger::log( "Telegram: Sending $type notification for booking $booking_id" );

		// wp_remote_get( "https://api.telegram.org/bot$bot_token/sendMessage?..." );
	}
}
