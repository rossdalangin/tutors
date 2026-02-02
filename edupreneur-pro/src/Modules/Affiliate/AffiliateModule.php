<?php
namespace EdupreneurPro\Modules\Affiliate;

use EdupreneurPro\Core\Modules\ModuleInterface;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\Affiliate\Services\AffiliateManager;
use EdupreneurPro\Modules\Affiliate\Controllers\AffiliateController;

class AffiliateModule implements ModuleInterface {
	private $container;
	public function __construct( Container $container ) { $this->container = $container; }
	public function init() {
		$this->container->set( 'affiliate_manager', new AffiliateManager() );

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}
	public function register_routes() {
		$controller = new AffiliateController();
		$controller->register_routes();
	}
	public function get_id() { return 'affiliate'; }
}
