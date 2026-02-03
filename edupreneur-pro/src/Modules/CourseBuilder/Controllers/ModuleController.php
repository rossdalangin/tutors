<?php
namespace EdupreneurPro\Modules\CourseBuilder\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use EdupreneurPro\Modules\CourseBuilder\Repositories\ModuleRepository;

class ModuleController extends WP_REST_Controller {
	protected $namespace = 'edupreneur/v1';
	protected $rest_base = 'modules';
	private $repository;

	public function __construct() {
		$this->repository = new ModuleRepository();
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => function() { return current_user_can( 'read' ); },
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => function() { return current_user_can( 'manage_edu_courses' ); },
			),
		) );
	}

	public function get_items( $request ) {
		$course_id = intval( $request['course_id'] );
		return new WP_REST_Response( $this->repository->get_by_course( $course_id ), 200 );
	}

	public function create_item( $request ) {
		$data = array(
			'course_id' => intval( $request['course_id'] ),
			'title'     => sanitize_text_field( $request['title'] ),
		);
		$id = $this->repository->create( $data );
		return new WP_REST_Response( array( 'id' => $id ), 201 );
	}
}
