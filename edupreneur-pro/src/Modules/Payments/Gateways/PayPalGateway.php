<?php
namespace EdupreneurPro\Modules\Payments\Gateways;

use EdupreneurPro\Modules\Payments\Interfaces\PaymentGatewayInterface;

class PayPalGateway implements PaymentGatewayInterface {
	public function process_payment( $data ) {
		return array(
			'success'        => true,
			'transaction_id' => 'PAYID-' . bin2hex( random_bytes( 8 ) ),
			'status'         => 'approved',
			'gateway'        => 'paypal'
		);
	}
	public function get_id() { return 'paypal'; }
}
