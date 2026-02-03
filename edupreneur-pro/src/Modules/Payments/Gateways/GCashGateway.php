<?php
namespace EdupreneurPro\Modules\Payments\Gateways;

use EdupreneurPro\Modules\Payments\Interfaces\PaymentGatewayInterface;

class GCashGateway implements PaymentGatewayInterface {
	public function process_payment( $data ) {
		return array(
			'success'        => true,
			'transaction_id' => 'GC-' . bin2hex( random_bytes( 8 ) ),
			'status'         => 'paid',
			'gateway'        => 'gcash'
		);
	}
	public function get_id() { return 'gcash'; }
}
