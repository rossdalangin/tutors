<?php
namespace EdupreneurPro\Modules\CourseBuilder\Controllers;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;

class CourseController extends WP_REST_Controller {
	protected $namespace = 'edupreneur/v1';
	protected $rest_base = 'courses';

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'check_manage_permission' ),
			),
		) );
	}

	public function check_read_permission() { return current_user_can( 'read' ); }
	public function check_manage_permission() { return current_user_can( 'manage_edu_courses' ); }

	public function get_items( $request ) { return new WP_REST_Response( array(), 200 ); }
	public function create_item( $request ) {
		// Secure input
		$title = sanitize_text_field( $request['title'] );
		return new WP_REST_Response( array( 'title' => $title ), 201 );
	}
}
