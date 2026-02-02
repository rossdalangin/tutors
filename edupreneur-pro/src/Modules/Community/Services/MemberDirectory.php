<?php
namespace EdupreneurPro\Modules\Community\Services;

class MemberDirectory {
	public function get_course_members( $course_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "SELECT u.ID, u.display_name FROM {$wpdb->users} u JOIN {$wpdb->prefix}edu_enrollments e ON u.ID = e.student_id WHERE e.course_id = %d", $course_id ) );
	}
}
