<?php
namespace EdupreneurPro\Modules\CourseBuilder;

class ProgressTracker {
	private $table;
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'edu_progress';
	}
	public function mark_as_completed( $student_id, $course_id, $lesson_id ) {
		global $wpdb;
		return $wpdb->insert( $this->table, array(
			'student_id'   => $student_id,
			'course_id'    => $course_id,
			'lesson_id'    => $lesson_id,
			'completed'    => 1,
			'completed_at' => current_time( 'mysql' ),
		) );
	}
}
