<?php
namespace EdupreneurPro\Modules\CourseBuilder\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use EdupreneurPro\Modules\CourseBuilder\Repositories\LessonRepository;

class LessonController extends WP_REST_Controller {
	protected $namespace = 'edupreneur/v1';
	protected $rest_base = 'lessons';
	private $repository;

	public function __construct() {
		$this->repository = new LessonRepository();
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
				'permission_callback' => function() { return current_user_can( 'manage_edu_lessons' ); },
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/reorder', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'reorder_items' ),
			'permission_callback' => function() { return current_user_can( 'manage_edu_lessons' ); },
		) );
	}

	public function reorder_items( $request ) {
		$ids = $request->get_param( 'ids' );
		$module_id = intval( $request->get_param( 'module_id' ) );
		if ( ! is_array( $ids ) ) {
			return new \WP_Error( 'invalid_data', 'Expected array of IDs', array( 'status' => 400 ) );
		}

		global $wpdb;
		foreach ( $ids as $index => $id ) {
			$wpdb->update(
				"{$wpdb->prefix}edu_lessons",
				array(
					'order_index' => $index,
					'module_id'   => $module_id
				),
				array( 'id' => intval( $id ) ),
				array( '%d', '%d' ),
				array( '%d' )
			);
		}

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function get_items( $request ) {
		$course_id = intval( $request['course_id'] );
		return new WP_REST_Response( $this->repository->get_by_course( $course_id ), 200 );
	}

	public function create_item( $request ) {
		$data = array(
			'course_id' => intval( $request['course_id'] ),
			'module_id' => intval( $request['module_id'] ),
			'title'     => sanitize_text_field( $request['title'] ),
			'content'   => wp_kses_post( $request['description'] ),
			'video_url' => esc_url_raw( $request['video_url'] ),
			'drip_days' => intval( $request['drip_days'] ),
		);
		$id = $this->repository->create( $data );
		return new WP_REST_Response( array( 'id' => $id ), 201 );
	}
}
