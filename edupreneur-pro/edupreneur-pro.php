<?php
/**
 * Plugin Name: EdupreneurPro
 * @package EdupreneurPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

final class EdupreneurPro {
	private static $instance;
	public $container;

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new EdupreneurPro();
			self::$instance->setup();
		}
		return self::$instance;
	}

	private function setup() {
		$this->define_constants();
		$this->init_container();
		$this->init_modules();
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	private function define_constants() {
		define( 'EDUPRENEUR_PRO_VERSION', '1.0.0' );
		define( 'EDUPRENEUR_PRO_PATH', plugin_dir_path( __FILE__ ) );
	}

	private function init_container() {
		$this->container = new \EdupreneurPro\Core\Container();
	}

	private function init_modules() {
		$module_manager = new \EdupreneurPro\Core\Modules\ModuleManager( $this->container );
		$this->container->set( 'modules', $module_manager );
		$module_manager->init();
	}

	public function activate() {
		\EdupreneurPro\Core\Database\Migrations::run();
		\EdupreneurPro\Core\Auth\Roles::register();
		flush_rewrite_rules();
	}

	public function on_plugins_loaded() {}
}

function EdupreneurPro() {
	return EdupreneurPro::instance();
}
EdupreneurPro();
