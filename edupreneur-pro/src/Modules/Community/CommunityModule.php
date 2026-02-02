<?php
namespace EdupreneurPro\Modules\Community;

use EdupreneurPro\Core\Modules\ModuleInterface;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\Community\Services\DiscussionBoard;
use EdupreneurPro\Modules\Community\Controllers\CommunityController;

class CommunityModule implements ModuleInterface {
	private $container;
	public function __construct( Container $container ) { $this->container = $container; }
	public function init() {
		$this->container->set( 'discussion_board', new DiscussionBoard() );

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}
	public function register_routes() {
		$controller = new CommunityController();
		$controller->register_routes();
	}
	public function get_id() { return 'community'; }
}
