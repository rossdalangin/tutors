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

		// Clear first to avoid duplicates if desired, or just append
		// self::clear_database();

		// Add a sample course
		$wpdb->insert( "{$wpdb->prefix}edu_courses", array(
			'title'       => 'Mastering Digital Entrepreneurship',
			'description' => 'A comprehensive guide to building a scalable online business from scratch.',
			'price'       => 199.99,
			'status'      => 'published',
			'instructor_id' => get_current_user_id()
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

		// Add a sample student/enrollment if there are other users,
		// but let's just stick to courses for now as requested.
	}
}
