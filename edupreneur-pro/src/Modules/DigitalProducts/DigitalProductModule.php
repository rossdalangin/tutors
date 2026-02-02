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
		add_action( 'init', array( $this, 'handle_download' ) );
	}

	public function handle_download() {
		if ( isset( $_GET['edu_download'] ) ) {
			$token = sanitize_text_field( $_GET['edu_download'] );
			$manager = $this->container->get( 'download_manager' );
			$data = $manager->verify_token( $token );
			if ( $data ) {
				// In a real system, we'd serve the file here.
				wp_die( 'Download verified! Serving file for product ID: ' . intval( $data['product_id'] ) );
			} else {
				wp_die( 'Invalid or expired download link.' );
			}
		}
	}
	public function get_id() { return 'digital-products'; }
}
