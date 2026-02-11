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

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/posts/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_post' ),
				'permission_callback' => function() { return is_user_logged_in(); },
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_post' ),
				'permission_callback' => function() { return is_user_logged_in(); },
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/messages', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_messages' ),
				'permission_callback' => function() { return is_user_logged_in(); },
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_message' ),
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
		if ( isset( $request['course_id'] ) ) {
			$posts = $board->get_posts( intval( $request['course_id'] ) );
		} else {
			global $wpdb;
			$posts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_community_posts ORDER BY created_at DESC" );
		}
		return new WP_REST_Response( $posts, 200 );
	}

	public function update_post( $request ) {
		$id = $request['id'];
		$board = new DiscussionBoard();
		$data = array();
		if ( isset( $request['content'] ) ) $data['content'] = sanitize_textarea_field( $request['content'] );
		if ( isset( $request['is_pinned'] ) ) $data['is_pinned'] = intval( $request['is_pinned'] );

		$board->update_post( $id, $data );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function delete_post( $request ) {
		$id = $request['id'];
		$board = new DiscussionBoard();
		$board->delete_post( $id );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function get_messages( $request ) {
		$board = new DiscussionBoard();
		$user_id = get_current_user_id();
		$other_user_id = isset( $request['to'] ) ? intval( $request['to'] ) : 0;

		$messages = $board->get_direct_messages( $user_id, $other_user_id );
		return new WP_REST_Response( $messages, 200 );
	}

	public function create_message( $request ) {
		$board = new DiscussionBoard();
		$data = array(
			'recipient_id' => intval( $request['recipient_id'] ),
			'content'      => sanitize_textarea_field( $request['content'] ),
			'course_id'    => 0,
		);
		$id = $board->create_post( $data );
		return new WP_REST_Response( array( 'id' => $id ), 201 );
	}
}
