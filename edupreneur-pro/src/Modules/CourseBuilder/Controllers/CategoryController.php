<?php
namespace EdupreneurPro\Modules\CourseBuilder\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use EdupreneurPro\Modules\CourseBuilder\Repositories\CategoryRepository;

class CategoryController extends WP_REST_Controller {
	protected $namespace = 'edupreneur/v1';
	protected $rest_base = 'categories';
	private $repository;

	public function __construct() {
		$this->repository = new CategoryRepository();
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => '__return_true',
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'check_permission' ),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => '__return_true',
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'check_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'check_permission' ),
			),
		) );
	}

	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	public function get_items( $request ) {
		return new WP_REST_Response( $this->repository->all(), 200 );
	}

	public function get_item( $request ) {
		return new WP_REST_Response( $this->repository->find( $request['id'] ), 200 );
	}

	public function create_item( $request ) {
		$data = array(
			'name'        => sanitize_text_field( $request['name'] ),
			'slug'        => sanitize_title( $request['name'] ),
			'description' => sanitize_textarea_field( $request['description'] ),
		);
		$id = $this->repository->create( $data );
		return new WP_REST_Response( array( 'id' => $id ), 201 );
	}

	public function update_item( $request ) {
		$data = array();
		if ( isset( $request['name'] ) ) {
			$data['name'] = sanitize_text_field( $request['name'] );
			$data['slug'] = sanitize_title( $request['name'] );
		}
		if ( isset( $request['description'] ) ) {
			$data['description'] = sanitize_textarea_field( $request['description'] );
		}
		$this->repository->update( $request['id'], $data );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function delete_item( $request ) {
		$this->repository->delete( $request['id'] );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}
}
