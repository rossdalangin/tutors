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

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'check_manage_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'check_manage_permission' ),
			),
		) );
	}

	public function check_read_permission() { return current_user_can( 'read' ); }
	public function check_manage_permission() { return current_user_can( 'manage_edu_courses' ); }

	public function get_items( $request ) {
		return new WP_REST_Response( $this->repository->all(), 200 );
	}

	public function get_item( $request ) {
		$id = $request['id'];
		return new WP_REST_Response( $this->repository->find( $id ), 200 );
	}

	public function create_item( $request ) {
		global $wpdb;
		$category_id = isset( $request['category_id'] ) ? intval( $request['category_id'] ) : 0;
		$category_name = 'General';
		if ( $category_id ) {
			$category_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}edu_categories WHERE id = %d", $category_id ) ) ?: 'General';
		}

		$data = array(
			'title'         => sanitize_text_field( $request['title'] ),
			'description'   => wp_kses_post( $request['description'] ),
			'category'      => $category_name,
			'category_id'   => $category_id,
			'price'         => isset( $request['price'] ) ? floatval( $request['price'] ) : 0.00,
			'slug'          => sanitize_title( $request['title'] ),
			'instructor_id' => get_current_user_id(),
			'status'        => 'publish',
		);
		$id = $this->repository->create( $data );
		return new WP_REST_Response( array( 'id' => $id ), 201 );
	}

	public function update_item( $request ) {
		global $wpdb;
		$id = $request['id'];
		$data = array();
		if ( isset( $request['title'] ) ) {
			$data['title'] = sanitize_text_field( $request['title'] );
			$data['slug'] = sanitize_title( $request['title'] );
		}
		if ( isset( $request['description'] ) ) $data['description'] = wp_kses_post( $request['description'] );

		if ( isset( $request['category_id'] ) ) {
			$category_id = intval( $request['category_id'] );
			$data['category_id'] = $category_id;
			$category_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}edu_categories WHERE id = %d", $category_id ) ) ?: 'General';
			$data['category'] = $category_name;
		} elseif ( isset( $request['category'] ) ) {
			$data['category'] = sanitize_text_field( $request['category'] );
		}

		if ( isset( $request['price'] ) ) $data['price'] = floatval( $request['price'] );

		$this->repository->update( $id, $data );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function delete_item( $request ) {
		$id = $request['id'];
		$this->repository->delete( $id );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}
}
