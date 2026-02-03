<?php
namespace EdupreneurPro\Modules\Payments\Repositories;

class OrderRepository {
	private $table;
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'edu_orders';
	}
	public function create( $data ) {
		global $wpdb;
		return $wpdb->insert( $this->table, $data ) ? $wpdb->insert_id : false;
	}

	public function all() {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$this->table} ORDER BY created_at DESC" );
	}

	public function update( $id, $data ) {
		global $wpdb;
		return $wpdb->update( $this->table, $data, array( 'id' => $id ) );
	}

	public function delete( $id ) {
		global $wpdb;
		return $wpdb->delete( $this->table, array( 'id' => $id ) );
	}
}
