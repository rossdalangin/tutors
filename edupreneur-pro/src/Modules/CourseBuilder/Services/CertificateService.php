<?php
namespace EdupreneurPro\Modules\CourseBuilder\Services;

class CertificateService {
	public function generate_certificate( $user_id, $course_id ) {
		// In a real app, we'd use a library like Dompdf or TCPDF here
		$user = get_userdata( $user_id );
		$course_title = "Course ID: " . $course_id; // Simplified

		return array(
			'user'         => $user->display_name,
			'course'       => $course_title,
			'issued_at'    => current_time( 'mysql' ),
			'verify_token' => wp_generate_password( 12, false )
		);
	}
}
