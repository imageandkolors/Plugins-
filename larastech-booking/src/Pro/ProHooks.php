<?php

namespace Larastech\Booking\Pro;

/**
 * Pro Hooks class.
 *
 * Connects Free plugin events to Pro features.
 */
class ProHooks {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		// Hook into Free plugin events.
		add_action( 'lt_booking_created', [ __CLASS__, 'on_booking_created' ], 10, 2 );
		add_action( 'lt_booking_updated', [ __CLASS__, 'on_booking_updated' ], 10, 2 );

		// Register Pro async delivery hooks.
		add_action( 'lt_booking_whatsapp_notify', [ 'Larastech\Booking\Pro\WhatsAppNotifier', 'deliver' ], 10, 2 );
		add_action( 'lt_booking_telegram_notify', [ 'Larastech\Booking\Pro\TelegramNotifier', 'deliver' ], 10, 2 );
	}

	/**
	 * Trigger Pro actions on booking creation.
	 */
	public static function on_booking_created( $booking_id, $data ) {
		if ( ! LicenseManager::is_active() ) return;

		// 1. WhatsApp Notification.
		WhatsAppNotifier::send_notification( $booking_id, 'created' );

		// 2. Telegram Notification.
		TelegramNotifier::send_notification( $booking_id, 'created' );

		// 3. Google Sheets Sync.
		GoogleSheetsSync::sync_booking( $booking_id );
	}

	/**
	 * Trigger Pro actions on booking update.
	 */
	public static function on_booking_updated( $booking_id, $status ) {
		if ( ! LicenseManager::is_active() ) return;

		WhatsAppNotifier::send_notification( $booking_id, $status );
		TelegramNotifier::send_notification( $booking_id, $status );
		GoogleSheetsSync::sync_booking( $booking_id );
	}
}
