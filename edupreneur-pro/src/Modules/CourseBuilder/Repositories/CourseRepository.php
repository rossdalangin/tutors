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
		$courses = $wpdb->get_results( "SELECT * FROM {$this->table} ORDER BY created_at DESC" );
		foreach ( $courses as $course ) {
			$course->modules = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_modules WHERE course_id = %d ORDER BY order_index ASC", $course->id ) );
			foreach ( $course->modules as $module ) {
				$module->lessons = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_lessons WHERE module_id = %d ORDER BY order_index ASC", $module->id ) );
			}
			$course->orphan_lessons = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_lessons WHERE course_id = %d AND (module_id = 0 OR module_id IS NULL) ORDER BY order_index ASC", $course->id ) );
		}
		return $courses;
	}

	public function find( $id ) {
		global $wpdb;
		$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id ) );
		if ( $course && $course->course_type === 'bundle' ) {
			$course->bundle_items = $wpdb->get_results( $wpdb->prepare( "SELECT course_id FROM {$wpdb->prefix}edu_bundle_items WHERE bundle_id = %d", $id ) );
		}
		return $course;
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
