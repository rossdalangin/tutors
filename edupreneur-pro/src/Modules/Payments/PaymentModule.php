<?php
namespace EdupreneurPro\Modules\Payments;

use EdupreneurPro\Core\Modules\ModuleInterface;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\Payments\Repositories\OrderRepository;
use EdupreneurPro\Modules\Payments\Gateways\StripeGateway;
use EdupreneurPro\Modules\Payments\Controllers\PaymentController;

class PaymentModule implements ModuleInterface {
	private $container;
	public function __construct( Container $container ) { $this->container = $container; }
	public function init() {
		$this->container->set( 'order_repository', new OrderRepository() );
		$this->container->set( 'stripe_gateway', new StripeGateway() );

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}
	public function register_routes() {
		$controller = new PaymentController();
		$controller->register_routes();

		$order_controller = new \EdupreneurPro\Modules\Payments\Controllers\OrderController();
		$order_controller->register_routes();
	}
	public function get_id() { return 'payments'; }
}
