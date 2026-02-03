<?php

namespace EdupreneurPro\Modules\Affiliate\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use EdupreneurPro\Modules\Affiliate\Services\AffiliateDashboard;

class AffiliateController extends WP_REST_Controller {

	protected $namespace = 'edupreneur/v1';
	protected $rest_base = 'affiliates';

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/stats', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => function() { return is_user_logged_in(); },
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/register', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'register_affiliate' ),
				'permission_callback' => function() { return is_user_logged_in(); },
			),
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

	public function register_affiliate( $request ) {
		$manager = new \EdupreneurPro\Modules\Affiliate\Services\AffiliateManager();
		$id = $manager->register_affiliate( get_current_user_id() );
		return new WP_REST_Response( array( 'id' => $id ), 201 );
	}

	public function get_stats( $request ) {
		$dashboard = new AffiliateDashboard();
		$stats = $dashboard->get_stats( get_current_user_id() );
		return new WP_REST_Response( $stats, 200 );
	}

	public function get_items( $request ) {
		$repo = new \EdupreneurPro\Modules\Affiliate\Repositories\AffiliateRepository();
		return new WP_REST_Response( $repo->all(), 200 );
	}

	public function update_item( $request ) {
		$id = $request['id'];
		$data = array();
		if ( isset( $request['status'] ) ) $data['status'] = sanitize_text_field( $request['status'] );
		if ( isset( $request['commission_rate'] ) ) $data['commission_rate'] = floatval( $request['commission_rate'] );

		$repo = new \EdupreneurPro\Modules\Affiliate\Repositories\AffiliateRepository();
		$repo->update( $id, $data );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	public function delete_item( $request ) {
		$id = $request['id'];
		$repo = new \EdupreneurPro\Modules\Affiliate\Repositories\AffiliateRepository();
		$repo->delete( $id );
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}
}
