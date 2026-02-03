<?php
namespace EdupreneurPro\Modules\CourseBuilder\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use EdupreneurPro\Modules\CourseBuilder\Repositories\CourseRepository;

class CourseController extends WP_REST_Controller {
	protected $namespace = 'edupreneur/v1';
	protected $rest_base = 'courses';
	private $repository;

	public function __construct() {
		$this->repository = new CourseRepository();
	}

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

	public function get_items( $request ) {
		return new WP_REST_Response( $this->repository->all(), 200 );
	}

	public function create_item( $request ) {
		$data = array(
			'title'         => sanitize_text_field( $request['title'] ),
			'description'   => wp_kses_post( $request['description'] ),
			'slug'          => sanitize_title( $request['title'] ),
			'instructor_id' => get_current_user_id(),
			'status'        => 'publish',
		);
		$id = $this->repository->create( $data );
		return new WP_REST_Response( array( 'id' => $id ), 201 );
	}
}
