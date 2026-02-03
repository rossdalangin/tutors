<?php
namespace EdupreneurPro\Modules\Payments\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use EdupreneurPro\Modules\Payments\Repositories\OrderRepository;

class OrderController extends WP_REST_Controller {
	protected $namespace = 'edupreneur/v1';
	protected $rest_base = 'orders';
	private $repository;

	public function __construct() {
		$this->repository = new OrderRepository();
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => function() { return current_user_can( 'manage_options' ); },
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => function() { return current_user_can( 'manage_options' ); },
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => function() { return current_user_can( 'manage_options' ); },
			),
		) );
	}

	public function get_items( $request ) {
		return new WP_REST_Response( $this->repository->all(), 200 );
	}

	public function update_item( $request ) {
		$id = $request['id'];
		$data = array();
		if ( isset( $request['status'] ) ) $data['status'] = sanitize_text_field( $request['status'] );

		$this->repository->update( $id, $data );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function delete_item( $request ) {
		$id = $request['id'];
		$this->repository->delete( $id );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}
}
