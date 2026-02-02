<?php
namespace EdupreneurPro\Modules\Payments\Interfaces;

interface PaymentGatewayInterface {
	public function process_payment( $data );
	public function get_id();
}
