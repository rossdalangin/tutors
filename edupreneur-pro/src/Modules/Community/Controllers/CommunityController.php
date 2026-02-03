<?php

namespace EdupreneurPro\Modules\Community\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use EdupreneurPro\Modules\Community\Services\DiscussionBoard;

class CommunityController extends WP_REST_Controller {

	protected $namespace = 'edupreneur/v1';
	protected $rest_base = 'community';

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/posts', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_posts' ),
				'permission_callback' => function() { return is_user_logged_in(); },
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_post' ),
				'permission_callback' => function() { return is_user_logged_in(); },
			),
		) );
	}

	public function create_post( $request ) {
		$board = new DiscussionBoard();
		$data = array(
			'course_id' => intval( $request['course_id'] ),
			'content'   => sanitize_textarea_field( $request['content'] ),
		);
		$id = $board->create_post( $data );
		return new WP_REST_Response( array( 'id' => $id ), 201 );
	}

	public function get_posts( $request ) {
		$board = new DiscussionBoard();
		$posts = $board->get_posts( intval( $request['course_id'] ) );
		return new WP_REST_Response( $posts, 200 );
	}
}
