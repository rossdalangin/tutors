<?php
namespace EdupreneurPro\Core\Modules;
use EdupreneurPro\Core\Container;
class ModuleManager {
	private $container;
	private $modules = array();
	public function __construct( Container $container ) { $this->container = $container; }
	public function init() {
		$module_classes = array(
			'EdupreneurPro\Modules\CourseBuilder\CourseBuilderModule',
			'EdupreneurPro\Modules\Community\CommunityModule',
			'EdupreneurPro\Modules\Payments\PaymentModule',
			'EdupreneurPro\Modules\Affiliate\AffiliateModule',
			'EdupreneurPro\Modules\Dashboard\DashboardModule',
			'EdupreneurPro\Modules\DigitalProducts\DigitalProductModule',
		);
		foreach ( $module_classes as $class ) {
			if ( class_exists( $class ) ) {
				$module = new $class( $this->container );
				$module->init();
				$this->modules[ $module->get_id() ] = $module;
			}
		}
	}
}
