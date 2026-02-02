<?php
namespace EdupreneurPro\Modules\Payments;

use EdupreneurPro\Core\Modules\ModuleInterface;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\Payments\Repositories\OrderRepository;
use EdupreneurPro\Modules\Payments\Gateways\StripeGateway;

class PaymentModule implements ModuleInterface {
	private $container;
	public function __construct( Container $container ) { $this->container = $container; }
	public function init() {
		$this->container->set( 'order_repository', new OrderRepository() );
		$this->container->set( 'stripe_gateway', new StripeGateway() );
	}
	public function get_id() { return 'payments'; }
}
