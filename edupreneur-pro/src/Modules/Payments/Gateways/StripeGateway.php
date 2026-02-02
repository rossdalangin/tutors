<?php
namespace EdupreneurPro\Modules\Payments\Gateways;

use EdupreneurPro\Modules\Payments\Interfaces\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface {
	public function process_payment( $data ) {
		return array( 'success' => true, 'transaction_id' => 'st_' . uniqid() );
	}
	public function get_id() { return 'stripe'; }
}
