<?php

namespace EdupreneurPro\Core;

class SystemService {

	public static function clear_database() {
		global $wpdb;
		$tables = array(
			'edu_courses',
			'edu_modules',
			'edu_lessons',
			'edu_enrollments',
			'edu_orders',
			'edu_payments',
			'edu_progress',
			'edu_affiliates',
			'edu_commissions',
			'edu_community_posts',
			'edu_quizzes',
			'edu_assignments'
		);

		foreach ( $tables as $table ) {
			$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}$table" );
		}
	}

	public static function add_sample_data() {
		global $wpdb;

		// Create sample users if they don't exist
		$users = array(
			array( 'user_login' => 'tutor_demo', 'user_pass' => 'demo123', 'role' => 'administrator' ),
			array( 'user_login' => 'student_demo', 'user_pass' => 'demo123', 'role' => 'student' ),
			array( 'user_login' => 'affiliate_demo', 'user_pass' => 'demo123', 'role' => 'affiliate' )
		);

		foreach ( $users as $u ) {
			if ( ! username_exists( $u['user_login'] ) ) {
				$user_id = wp_create_user( $u['user_login'], $u['user_pass'] );
				$user = new \WP_User( $user_id );
				$user->set_role( $u['role'] );
			} else {
				$user = get_user_by( 'login', $u['user_login'] );
				$user->set_role( $u['role'] );
			}
		}

		$instructor_id = get_user_by( 'login', 'tutor_demo' )->ID;
		$student_id = get_user_by( 'login', 'student_demo' )->ID;

		// Add a sample course
		$wpdb->insert( "{$wpdb->prefix}edu_courses", array(
			'title'       => 'Mastering Digital Entrepreneurship',
			'description' => 'A comprehensive guide to building a scalable online business from scratch.',
			'price'       => 199.99,
			'status'      => 'published',
			'instructor_id' => $instructor_id
		) );
		$course_id = $wpdb->insert_id;

		// Add modules
		$modules = array( 'Mindset & Foundations', 'Product Development', 'Marketing Mastery' );
		foreach ( $modules as $index => $m_title ) {
			$wpdb->insert( "{$wpdb->prefix}edu_modules", array(
				'course_id'   => $course_id,
				'title'       => $m_title,
				'order_index' => $index
			) );
			$module_id = $wpdb->insert_id;

			// Add lessons
			for ( $i = 1; $i <= 3; $i++ ) {
				$wpdb->insert( "{$wpdb->prefix}edu_lessons", array(
					'course_id'   => $course_id,
					'module_id'   => $module_id,
					'title'       => "Lesson $i: Deep dive into " . strtolower( $m_title ),
					'content'     => 'In this lesson, we explore the core principles...',
					'lesson_type' => 'video',
					'order_index' => $i
				) );
			}
		}

		// Enrollment
		$wpdb->insert( "{$wpdb->prefix}edu_enrollments", array(
			'student_id' => $student_id,
			'course_id'  => $course_id,
			'status'     => 'active'
		) );

		// Order
		$wpdb->insert( "{$wpdb->prefix}edu_orders", array(
			'user_id'      => $student_id,
			'total_amount' => 199.99,
			'status'       => 'completed'
		) );

		// Order
		$wpdb->insert( "{$wpdb->prefix}edu_orders", array(
			'user_id'      => $student_id,
			'total_amount' => 199.99,
			'status'       => 'completed'
		) );
		$order_id = $wpdb->insert_id;

		// Affiliate
		$affiliate_user_id = get_user_by( 'login', 'affiliate_demo' )->ID;
		$wpdb->insert( "{$wpdb->prefix}edu_affiliates", array(
			'user_id'         => $affiliate_user_id,
			'referral_code'   => 'DEMO_REF',
			'commission_rate' => 15.00,
			'status'          => 'active'
		) );
		$affiliate_id = $wpdb->insert_id;

		// Sample Community Posts
		$wpdb->insert( "{$wpdb->prefix}edu_community_posts", array(
			'user_id'   => $instructor_id,
			'content'   => 'Welcome everyone to the Mastering Digital Entrepreneurship course!',
			'course_id' => 0,
			'is_pinned' => 1
		) );

		$wpdb->insert( "{$wpdb->prefix}edu_community_posts", array(
			'user_id'   => $student_id,
			'content'   => 'I am so excited to start learning. Who else is in?',
			'course_id' => 0
		) );

		// Sample Commissions
		$wpdb->insert( "{$wpdb->prefix}edu_commissions", array(
			'affiliate_id' => $affiliate_id,
			'order_id'     => $order_id,
			'amount'       => 29.99,
			'status'       => 'unpaid'
		) );
	}
}
