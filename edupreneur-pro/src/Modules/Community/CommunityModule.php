<?php
namespace EdupreneurPro\Modules\Community;

use EdupreneurPro\Core\Modules\ModuleInterface;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\Community\Services\DiscussionBoard;

class CommunityModule implements ModuleInterface {
	private $container;
	public function __construct( Container $container ) { $this->container = $container; }
	public function init() {
		$this->container->set( 'discussion_board', new DiscussionBoard() );
	}
	public function get_id() { return 'community'; }
}
