<?php
namespace EdupreneurPro\Modules\DigitalProducts;

use EdupreneurPro\Core\Modules\ModuleInterface;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\DigitalProducts\DownloadManager;

class DigitalProductModule implements ModuleInterface {
	private $container;
	public function __construct( Container $container ) { $this->container = $container; }
	public function init() {
		$this->container->set( 'download_manager', new DownloadManager() );
	}
	public function get_id() { return 'digital-products'; }
}
