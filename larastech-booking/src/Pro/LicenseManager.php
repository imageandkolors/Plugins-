<?php

namespace Larastech\Booking\Pro;

use Larastech\Booking\Common\Settings;
use Larastech\Booking\Common\Logger;

/**
 * License Manager class.
 */
class LicenseManager {

	/**
	 * Local storage key.
	 */
	const LICENSE_OPTION = 'lt_booking_license';

	/**
	 * Check if the current license is active.
	 */
	public static function is_active() {
		$license = get_option( self::LICENSE_OPTION );
		if ( ! $license || empty( $license['key'] ) ) {
			return false;
		}
		return isset( $license['status'] ) && 'active' === $license['status'];
	}

	/**
	 * Activate a license key.
	 */
	public static function activate( $key ) {
		// Mock remote validation call.
		// In production: $response = wp_remote_post( 'https://api.larastech.com/v1/license/activate', ... );

		if ( 'PRO-123-VALID' === $key ) {
			$data = [
				'key'         => $key,
				'status'      => 'active',
				'activated_at' => current_time( 'mysql' ),
			];
			update_option( self::LICENSE_OPTION, $data );
			return true;
		}

		return false;
	}

	/**
	 * Deactivate the current license.
	 */
	public static function deactivate() {
		delete_option( self::LICENSE_OPTION );
		return true;
	}

	/**
	 * Refresh license status from remote server.
	 */
	public static function refresh() {
		$license = get_option( self::LICENSE_OPTION );
		if ( ! $license ) return;

		// In production: perform remote check.
	}
}
