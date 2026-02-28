<?php

namespace Larastech\Booking\Core;

/**
 * Roles and Capabilities class.
 */
class Roles {

	/**
	 * Initialize roles and hooks.
	 */
	public static function init() {
		// Capability check logic or filtering if needed.
	}

	/**
	 * Register the custom role and capabilities.
	 */
	public static function register() {
		add_role(
			'booking_manager',
			__( 'Booking Manager', 'larastech-booking' ),
			[
				'read'                      => true,
				'lt_manage_bookings'        => true,
				'lt_manage_services'        => true,
				'lt_manage_staff'           => true,
				'lt_manage_settings'        => true,
				'lt_view_frontend_dash'      => true,
			]
		);

		// Add caps to administrator.
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'lt_manage_bookings' );
			$admin->add_cap( 'lt_manage_services' );
			$admin->add_cap( 'lt_manage_staff' );
			$admin->add_cap( 'lt_manage_settings' );
			$admin->add_cap( 'lt_view_frontend_dash' );
		}
	}
}
