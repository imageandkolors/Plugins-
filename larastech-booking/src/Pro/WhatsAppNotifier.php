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

		// Placeholder for WhatsApp Cloud API call.
		Logger::log( "WhatsApp: Sending $type notification for booking $booking_id" );

		// wp_remote_post( 'https://graph.facebook.com/v13.0/.../messages', ... );
	}
}
