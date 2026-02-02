<?php
namespace EdupreneurPro\Modules\Affiliate;

use EdupreneurPro\Core\Modules\ModuleInterface;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\Affiliate\Services\AffiliateManager;

class AffiliateModule implements ModuleInterface {
	private $container;
	public function __construct( Container $container ) { $this->container = $container; }
	public function init() {
		$this->container->set( 'affiliate_manager', new AffiliateManager() );
	}
	public function get_id() { return 'affiliate'; }
}
