<?php

namespace EdupreneurPro\Modules\Dashboard\Services;

/**
 * Email Automation Class
 */
class EmailAutomation {

	/**
	 * Send welcome email.
	 */
	public function send_welcome_email( $user_id ) {
		$user = get_userdata( $user_id );
		$subject = __( 'Welcome to our Learning Platform', 'edupreneur-pro' );
		$message = sprintf( __( 'Hello %s, welcome to our platform!', 'edupreneur-pro' ), $user->display_name );
		wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Send abandoned checkout email.
	 */
	public function send_abandoned_checkout_email( $user_id, $course_id ) {
		$user = get_userdata( $user_id );
		$course_title = get_the_title( $course_id ); // Assuming course is a post or similar
		$subject = __( 'You left something in your cart', 'edupreneur-pro' );
		$message = sprintf( __( 'Hello %s, you forgot to complete your purchase for %s.', 'edupreneur-pro' ), $user->display_name, $course_title );
		wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Send lesson reminder.
	 */
	public function send_lesson_reminder( $user_id, $lesson_id ) {
		$user = get_userdata( $user_id );
		$subject = __( 'Ready for your next lesson?', 'edupreneur-pro' );
		$message = sprintf( __( 'Hello %s, it is time for your next lesson!', 'edupreneur-pro' ), $user->display_name );
		wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Send completion follow-up.
	 */
	public function send_completion_followup( $user_id, $course_id ) {
		$user = get_userdata( $user_id );
		$subject = __( 'Congratulations on completing the course!', 'edupreneur-pro' );
		$message = sprintf( __( 'Hello %s, congratulations on completing the course! What would you like to learn next?', 'edupreneur-pro' ), $user->display_name );
		wp_mail( $user->user_email, $subject, $message );
	}
}
