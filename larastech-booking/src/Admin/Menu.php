<?php

namespace Larastech\Booking\Admin;

/**
 * Admin Menu class.
 */
class Menu {

	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
	}

	public static function register_menu() {
		add_menu_page(
			'Larastech Booking',
			'LT Booking',
			'lt_manage_bookings',
			'larastech-booking',
			[ __CLASS__, 'render_admin_page' ],
			'dashicons-calendar-alt',
			25
		);
	}

	public static function render_admin_page() {
		?>
		<div class="wrap">
			<h1>Larastech Booking</h1>
			<p>Modern Frontend Service Scheduler.</p>

			<div class="card" style="max-width: 600px;">
				<h2>Shortcodes</h2>
				<p>Copy and paste these shortcodes into any page or post:</p>
				<table class="widefat">
					<thead>
						<tr>
							<th>Feature</th>
							<th>Shortcode</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Customer Booking SPA</td>
							<td><code>[lt_booking_app]</code></td>
						</tr>
						<tr>
							<td>Admin Dashboard SPA</td>
							<td><code>[lt_admin_app]</code></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="card" style="max-width: 600px; margin-top: 20px;">
				<h2>Access Dashboards</h2>
				<p>You can also access the dashboards directly if you have placed them on pages:</p>
				<p>
					<a href="<?php echo esc_url( home_url( '/booking' ) ); ?>" class="button button-primary" target="_blank">View Customer Booking Page</a>
					<a href="<?php echo esc_url( home_url( '/booking-admin' ) ); ?>" class="button button-secondary" target="_blank">View Admin Dashboard</a>
				</p>
			</div>
		</div>
		<?php
	}
}
