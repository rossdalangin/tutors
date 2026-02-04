<?php
namespace EdupreneurPro\Modules\CourseBuilder\Repositories;

class CategoryRepository {
	private $table;
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'edu_categories';
	}
	public function all() {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$this->table} ORDER BY name ASC" );
	}
	public function find( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ) );
	}
	public function create( $data ) {
		global $wpdb;
		$inserted = $wpdb->insert( $this->table, $data );
		return $inserted ? $wpdb->insert_id : false;
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
