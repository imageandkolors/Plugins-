<?php

namespace Larastech\Booking\Pro;

/**
 * Payment Gateway Manager class.
 */
class PaymentGatewayManager {

	/**
	 * Initiate payment.
	 */
	public function initiate_payment( $booking_id, $gateway = 'stripe' ) {
		// Modular support for Stripe, PayPal, WooCommerce, etc.
		return [
			'status'      => 'pending',
			'payment_url' => 'https://checkout.stripe.com/...',
		];
	}

	/**
	 * Verify payment.
	 */
	public function verify_payment( $transaction_id ) {
		return true;
	}
}
