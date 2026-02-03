<?php
namespace EdupreneurPro\Modules\CourseBuilder\Repositories;
class CourseRepository {
	private $table;
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'edu_courses';
	}
	public function all() {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$this->table} ORDER BY created_at DESC" );
	}

	public function create( $data ) {
		global $wpdb;
		$inserted = $wpdb->insert( $this->table, $data );
		return $inserted ? $wpdb->insert_id : false;
	}
}
