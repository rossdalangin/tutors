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
				'args'                => array(
					'course_id' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
					'module_id' => array( 'required' => false, 'sanitize_callback' => 'intval' ),
				),
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

		register_rest_route( $this->namespace, '/notes', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'save_note' ),
			'permission_callback' => function() { return is_user_logged_in(); },
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
				'permission_callback' => function() { return current_user_can( 'manage_edu_lessons' ); },
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => function() { return current_user_can( 'manage_edu_lessons' ); },
			),
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
		$course_id = intval( $request->get_param( 'course_id' ) );
		$module_id_param = $request->get_param( 'module_id' );

		if ( null !== $module_id_param ) {
			$module_id = intval( $module_id_param );
			$lessons = $this->repository->get_by_module( $course_id, $module_id );
		} else {
			$lessons = $this->repository->get_by_course( $course_id );
		}

		return new WP_REST_Response( is_array( $lessons ) ? $lessons : array(), 200 );
	}

	public function create_item( $request ) {
		$data = array(
			'course_id' => intval( $request['course_id'] ),
			'module_id' => intval( $request['module_id'] ),
			'title'     => sanitize_text_field( $request['title'] ),
			'content'   => wp_kses_post( $request['description'] ),
			'video_url'   => esc_url_raw( $request['video_url'] ),
			'drip_days'   => intval( $request['drip_days'] ),
			'lesson_type' => sanitize_text_field( $request['lesson_type'] ),
		);
		$id = $this->repository->create( $data );
		if ( ! $id ) {
			return new \WP_Error( 'db_error', 'Could not create lesson', array( 'status' => 500 ) );
		}

		global $wpdb;
		if ( isset( $request['quiz_data'] ) && ! empty( $request['quiz_data'] ) ) {
			$wpdb->insert( "{$wpdb->prefix}edu_quizzes", array(
				'lesson_id' => $id,
				'title'     => 'Quiz for ' . $data['title'],
				'questions' => sanitize_textarea_field( $request['quiz_data'] )
			) );
		}

		if ( isset( $request['assignment_data'] ) && ! empty( $request['assignment_data'] ) ) {
			$wpdb->insert( "{$wpdb->prefix}edu_assignments", array(
				'lesson_id'    => $id,
				'title'        => 'Assignment for ' . $data['title'],
				'instructions' => sanitize_textarea_field( $request['assignment_data'] )
			) );
		}

		if ( isset( $request['resources'] ) && is_array( $request['resources'] ) ) {
			foreach ( $request['resources'] as $res ) {
				$wpdb->insert( "{$wpdb->prefix}edu_resources", array(
					'lesson_id' => $id,
					'title'     => sanitize_text_field( $res['title'] ),
					'url'       => esc_url_raw( $res['url'] )
				) );
			}
		}

		return new WP_REST_Response( array( 'id' => $id ), 201 );
	}

	public function get_item( $request ) {
		$id = $request['id'];
		$lesson = $this->repository->find( $id );
		if ( $lesson ) {
			global $wpdb;
			$lesson->resources = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_resources WHERE lesson_id = %d", $id ) );
			$lesson->quiz = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_quizzes WHERE lesson_id = %d", $id ) );
			$lesson->assignment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_assignments WHERE lesson_id = %d", $id ) );
		}
		return new WP_REST_Response( $lesson, 200 );
	}

	public function update_item( $request ) {
		$id = intval( $request['id'] );
		$lesson = $this->repository->find( $id );
		if ( ! $lesson ) {
			return new \WP_Error( 'not_found', 'Lesson not found', array( 'status' => 404 ) );
		}

		$data = array();
		if ( isset( $request['title'] ) ) $data['title'] = sanitize_text_field( $request['title'] );
		if ( isset( $request['description'] ) ) $data['content'] = wp_kses_post( $request['description'] );
		if ( isset( $request['video_url'] ) ) $data['video_url'] = esc_url_raw( $request['video_url'] );
		if ( isset( $request['drip_days'] ) ) $data['drip_days'] = intval( $request['drip_days'] );
		if ( isset( $request['lesson_type'] ) ) $data['lesson_type'] = sanitize_text_field( $request['lesson_type'] );

		if ( ! empty( $data ) ) {
			$this->repository->update( $id, $data );
		}

		$display_title = isset( $data['title'] ) ? $data['title'] : $lesson->title;

		global $wpdb;
		if ( isset( $request['quiz_data'] ) ) {
			$wpdb->replace( "{$wpdb->prefix}edu_quizzes", array(
				'lesson_id' => $id,
				'title'     => 'Quiz for ' . $display_title,
				'questions' => sanitize_textarea_field( $request['quiz_data'] )
			) );
		}

		if ( isset( $request['assignment_data'] ) ) {
			$wpdb->replace( "{$wpdb->prefix}edu_assignments", array(
				'lesson_id'    => $id,
				'title'        => 'Assignment for ' . $display_title,
				'instructions' => sanitize_textarea_field( $request['assignment_data'] )
			) );
		}

		if ( isset( $request['resources'] ) && is_array( $request['resources'] ) ) {
			global $wpdb;
			$wpdb->delete( "{$wpdb->prefix}edu_resources", array( 'lesson_id' => $id ) );
			foreach ( $request['resources'] as $res ) {
				$wpdb->insert( "{$wpdb->prefix}edu_resources", array(
					'lesson_id' => $id,
					'title'     => sanitize_text_field( $res['title'] ),
					'url'       => esc_url_raw( $res['url'] )
				) );
			}
		}

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function delete_item( $request ) {
		$id = $request['id'];
		$this->repository->delete( $id );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function save_note( $request ) {
		global $wpdb;
		$user_id = get_current_user_id();
		$lesson_id = intval( $request['lesson_id'] );
		$content = sanitize_textarea_field( $request['content'] );

		$wpdb->replace( "{$wpdb->prefix}edu_notes", array(
			'user_id'   => $user_id,
			'lesson_id' => $lesson_id,
			'content'   => $content
		) );

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}
}
