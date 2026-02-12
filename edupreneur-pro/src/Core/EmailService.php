<?php
namespace EdupreneurPro\Core;

class EmailService {

	public static function send_welcome_email( $user_id, $course_id ) {
		global $wpdb;
		$user = get_userdata( $user_id );
		$course = $wpdb->get_row( $wpdb->prepare( "SELECT title FROM {$wpdb->prefix}edu_courses WHERE id = %d", $course_id ) );
		$course_title = $course ? $course->title : "your new course";

		$subject = sprintf( __( 'Welcome to %s!', 'edupreneur-pro' ), $course_title );
		$message = sprintf(
			__( "Hi %s,\n\nCongratulations! You have successfully enrolled in '%s'.\n\nLogin to your dashboard to start learning: %s\n\nHappy learning!\nEdupreneurPro Team", 'edupreneur-pro' ),
			$user->display_name,
			$course_title,
			home_url( '/dashboard/' )
		);

		return wp_mail( $user->user_email, $subject, $message );
	}

	public static function send_completion_email( $user_id, $course_id ) {
		global $wpdb;
		$user = get_userdata( $user_id );
		$course = $wpdb->get_row( $wpdb->prepare( "SELECT title FROM {$wpdb->prefix}edu_courses WHERE id = %d", $course_id ) );
		$course_title = $course ? $course->title : "the course";

		$subject = sprintf( __( 'Congratulations on completing %s!', 'edupreneur-pro' ), $course_title );
		$message = sprintf(
			__( "Great job %s!\n\nYou have successfully completed all lessons in '%s'.\n\nYou can now download your certificate of completion from your dashboard.\n\nKeep up the great work!\nEdupreneurPro Team", 'edupreneur-pro' ),
			$user->display_name,
			$course_title
		);

		return wp_mail( $user->user_email, $subject, $message );
	}
}
