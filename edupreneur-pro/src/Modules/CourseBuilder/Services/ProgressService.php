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

		// Progress logic: Check if previous lesson is completed
		$prev_lesson_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}edu_lessons WHERE course_id = %d AND order_index < %d ORDER BY order_index DESC LIMIT 1",
			$lesson->course_id, $lesson->order_index
		) );

		if ( $prev_lesson_id ) {
			$is_completed = $wpdb->get_var( $wpdb->prepare(
				"SELECT completed FROM {$wpdb->prefix}edu_progress WHERE student_id = %d AND lesson_id = %d",
				$user_id, $prev_lesson_id
			) );

			if ( ! $is_completed ) {
				return false; // Previous lesson not completed
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
