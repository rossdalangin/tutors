<?php
namespace EdupreneurPro\Modules\Affiliate\Repositories;

class AffiliateRepository {
	private $table;
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'edu_affiliates';
	}
	public function create( $data ) {
		global $wpdb;
		return $wpdb->insert( $this->table, $data ) ? $wpdb->insert_id : false;
	}
	public function find_by_user( $user_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE user_id = %d", $user_id ) );
	}

	public function all() {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$this->table}" );
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
