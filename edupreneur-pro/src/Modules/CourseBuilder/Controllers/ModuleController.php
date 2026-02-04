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
				'args'                => array(
					'course_id' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
				),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => function() { return current_user_can( 'manage_edu_courses' ); },
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/reorder', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'reorder_items' ),
			'permission_callback' => function() { return current_user_can( 'manage_edu_courses' ); },
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => function() { return current_user_can( 'read' ); },
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => function() { return current_user_can( 'manage_edu_courses' ); },
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => function() { return current_user_can( 'manage_edu_courses' ); },
			),
		) );
	}

	public function reorder_items( $request ) {
		$ids = $request->get_param( 'ids' );
		if ( ! is_array( $ids ) ) {
			return new \WP_Error( 'invalid_data', 'Expected array of IDs', array( 'status' => 400 ) );
		}

		global $wpdb;
		foreach ( $ids as $index => $id ) {
			$wpdb->update(
				"{$wpdb->prefix}edu_modules",
				array( 'order_index' => $index ),
				array( 'id' => intval( $id ) ),
				array( '%d' ),
				array( '%d' )
			);
		}

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function get_items( $request ) {
		$course_id = intval( $request['course_id'] );
		return new WP_REST_Response( $this->repository->get_by_course( $course_id ), 200 );
	}

	public function get_item( $request ) {
		return new WP_REST_Response( $this->repository->find( $request['id'] ), 200 );
	}

	public function create_item( $request ) {
		$data = array(
			'course_id' => intval( $request['course_id'] ),
			'title'     => sanitize_text_field( $request['title'] ),
		);
		$id = $this->repository->create( $data );
		return new WP_REST_Response( array( 'id' => $id ), 201 );
	}

	public function update_item( $request ) {
		$id = $request['id'];
		$data = array();
		if ( isset( $request['title'] ) ) $data['title'] = sanitize_text_field( $request['title'] );

		$this->repository->update( $id, $data );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function delete_item( $request ) {
		$id = $request['id'];
		$this->repository->delete( $id );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}
}
