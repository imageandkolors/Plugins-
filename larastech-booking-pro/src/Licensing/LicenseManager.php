<?php

namespace Larastech\BookingPro\Licensing;

/**
 * License Manager class.
 */
class LicenseManager {
	const LICENSE_OPTION = 'lt_booking_license';

	public static function is_active() {
		$license = get_option( self::LICENSE_OPTION );
		return isset( $license['status'] ) && 'active' === $license['status'];
	}

	public static function activate( $key ) {
		if ( 'PRO-123-VALID' === $key ) {
			update_option( self::LICENSE_OPTION, [ 'key' => $key, 'status' => 'active' ] );
			return true;
		}
		return false;
	}

	public static function deactivate() {
		delete_option( self::LICENSE_OPTION );
		return true;
	}
}
