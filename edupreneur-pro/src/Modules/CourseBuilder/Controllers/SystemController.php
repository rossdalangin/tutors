<?php

namespace EdupreneurPro\Modules\CourseBuilder\Controllers;

use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;
use EdupreneurPro\Core\SystemService;

class SystemController extends WP_REST_Controller {

	protected $namespace = 'edupreneur/v1';
	protected $rest_base = 'debug';

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/sample-data', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'load_sample_data' ),
				'permission_callback' => array( $this, 'check_permission' ),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/reset', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'reset_data' ),
				'permission_callback' => array( $this, 'check_permission' ),
			),
		) );
	}

	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	public function load_sample_data() {
		SystemService::add_sample_data();
		return new WP_REST_Response( array( 'message' => 'Sample data loaded successfully.' ), 200 );
	}

	public function reset_data() {
		SystemService::clear_database();
		return new WP_REST_Response( array( 'message' => 'All course data has been reset.' ), 200 );
	}
}
