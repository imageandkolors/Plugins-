<?php

namespace Larastech\Booking\Pro;

use Larastech\Booking\Common\Logger;
use Larastech\Booking\Common\Settings;

/**
 * WhatsApp Notifier class.
 */
class WhatsAppNotifier {

	/**
	 * Send WhatsApp notification.
	 */
	public static function send_notification( $booking_id, $type ) {
		$token = Settings::get( 'pro_whatsapp_token' );
		if ( ! $token ) return;

		// Async-style: Use non-blocking wp_remote_post or schedule a cron.
		// For high performance, we schedule a single event or use a queue.
		wp_schedule_single_event( time(), 'lt_booking_whatsapp_notify', [ $booking_id, $type ] );

		Logger::log( "WhatsApp: Queued $type notification for booking $booking_id" );
	}

	/**
	 * Actual delivery logic (called by cron).
	 */
	public static function deliver( $booking_id, $type ) {
		// wp_remote_post( '...', [ 'blocking' => true, 'timeout' => 5 ] );
	}
}
