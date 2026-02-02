<?php

namespace EdupreneurPro\Modules\Payments\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;

class PaymentController extends WP_REST_Controller {

	protected $namespace = 'edupreneur/v1';
	protected $rest_base = 'payments';

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/webhook', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'handle_webhook' ),
				'permission_callback' => '__return_true',
			),
		) );
	}

	public function handle_webhook( $request ) {
		return new WP_REST_Response( array( 'received' => true ), 200 );
	}
}
