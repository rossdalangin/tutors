<?php
namespace EdupreneurPro\Modules\CourseBuilder\Repositories;

class LessonRepository {
	private $table;
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'edu_lessons';
	}
	public function find( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ) );
	}
	public function get_by_course( $course_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE course_id = %d ORDER BY order_index ASC", $course_id ) );
	}
	public function create( $data ) {
		global $wpdb;
		return $wpdb->insert( $this->table, $data ) ? $wpdb->insert_id : false;
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
