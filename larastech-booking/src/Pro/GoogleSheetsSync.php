<?php

namespace Larastech\Booking\Pro;

use Larastech\Booking\Common\Logger;
use Larastech\Booking\Common\Settings;

/**
 * Google Sheets Sync class.
 */
class GoogleSheetsSync {

	/**
	 * Sync booking to Google Sheets.
	 */
	public static function sync_booking( $booking_id ) {
		$sheet_id = Settings::get( 'pro_google_sheet_id' );
		if ( ! $sheet_id ) return;

		// Placeholder for Google Sheets API call.
		Logger::log( "Google Sheets: Syncing booking $booking_id" );

		// Append row logic.
	}
}
