<?php
namespace EdupreneurPro\Modules\Payments\Gateways;

use EdupreneurPro\Modules\Payments\Interfaces\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface {
	public function process_payment( $data ) {
		// Simulate API delay
		$amount = isset( $data['amount'] ) ? floatval( $data['amount'] ) : 0;

		if ( $amount < 0 ) {
			return array( 'success' => false, 'error' => 'Invalid amount' );
		}

		return array(
			'success'        => true,
			'transaction_id' => 'ch_' . bin2hex( random_bytes( 12 ) ),
			'status'         => 'succeeded',
			'gateway'        => 'stripe'
		);
	}
	public function get_id() { return 'stripe'; }
}
