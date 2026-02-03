<?php
namespace EdupreneurPro\Modules\CourseBuilder\Services;

class ProgressService {
	public function can_access_lesson( $user_id, $lesson_id ) {
		global $wpdb;
		$lesson = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_lessons WHERE id = %d", $lesson_id ) );
		if ( ! $lesson ) return false;

		// Drip logic: Check enrollment date
		$enrollment = $wpdb->get_row( $wpdb->prepare( "SELECT enrolled_at FROM {$wpdb->prefix}edu_enrollments WHERE student_id = %d AND course_id = %d", $user_id, $lesson->course_id ) );
		if ( ! $enrollment ) return false;

		if ( $lesson->drip_days > 0 ) {
			$available_at = strtotime( $enrollment->enrolled_at . " + {$lesson->drip_days} days" );
			if ( time() < $available_at ) {
				return false; // Not yet available (Drip)
			}
		}

		return true;
	}

	public function mark_lesson_complete( $user_id, $lesson_id ) {
		global $wpdb;
		$lesson = $wpdb->get_row( $wpdb->prepare( "SELECT course_id FROM {$wpdb->prefix}edu_lessons WHERE id = %d", $lesson_id ) );

		return $wpdb->replace( "{$wpdb->prefix}edu_progress", array(
			'student_id'   => $user_id,
			'course_id'    => $lesson->course_id,
			'lesson_id'    => $lesson_id,
			'completed'    => 1,
			'completed_at' => current_time( 'mysql' )
		) );
	}
}
