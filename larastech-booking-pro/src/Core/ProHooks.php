<?php

namespace Larastech\BookingPro\Core;

/**
 * Pro Hooks class.
 */
class ProHooks {

	public static function init() {
		add_action( 'lt_booking_created', [ __CLASS__, 'on_booking_created' ], 10, 2 );
		add_action( 'lt_booking_updated', [ __CLASS__, 'on_booking_updated' ], 10, 2 );

		add_action( 'lt_booking_whatsapp_notify', [ 'Larastech\BookingPro\Integrations\WhatsAppNotifier', 'deliver' ], 10, 2 );
		add_action( 'lt_booking_telegram_notify', [ 'Larastech\BookingPro\Integrations\TelegramNotifier', 'deliver' ], 10, 2 );
	}

	public static function on_booking_created( $booking_id, $data ) {
		if ( ! \Larastech\BookingPro\Licensing\LicenseManager::is_active() ) return;
		\Larastech\BookingPro\Integrations\WhatsAppNotifier::send_notification( $booking_id, 'created' );
		\Larastech\BookingPro\Integrations\TelegramNotifier::send_notification( $booking_id, 'created' );
		\Larastech\BookingPro\Integrations\GoogleSheetsSync::sync_booking( $booking_id );
	}

	public static function on_booking_updated( $booking_id, $status ) {
		if ( ! \Larastech\BookingPro\Licensing\LicenseManager::is_active() ) return;
		\Larastech\BookingPro\Integrations\WhatsAppNotifier::send_notification( $booking_id, $status );
		\Larastech\BookingPro\Integrations\TelegramNotifier::send_notification( $booking_id, $status );
		\Larastech\BookingPro\Integrations\GoogleSheetsSync::sync_booking( $booking_id );
	}
}
