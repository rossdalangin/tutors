<?php

namespace EdupreneurPro\Core;

class SystemService {

	public static function clear_database() {
		global $wpdb;
		$tables = array(
			'edu_categories',
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
			'edu_assignments',
			'edu_resources',
			'edu_kb',
			'edu_assets'
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

		// Add sample categories
		$wpdb->insert( "{$wpdb->prefix}edu_categories", array( 'name' => 'Business', 'slug' => 'business', 'description' => 'Master the art of commerce and entrepreneurship.' ) );
		$cat_business_id = $wpdb->insert_id;
		$wpdb->insert( "{$wpdb->prefix}edu_categories", array( 'name' => 'Marketing', 'slug' => 'marketing', 'description' => 'Learn how to reach your audience and scale your growth.' ) );
		$cat_marketing_id = $wpdb->insert_id;

		// Add a sample course
		$wpdb->insert( "{$wpdb->prefix}edu_courses", array(
			'title'       => 'Mastering Digital Entrepreneurship',
			'description' => 'A comprehensive guide to building a scalable online business from scratch.',
			'category'    => 'Business',
			'category_id' => $cat_business_id,
			'price'       => 199.99,
			'status'      => 'publish',
			'instructor_id' => $instructor_id
		) );
		$course_id = $wpdb->insert_id;

		// Add an orphan introductory lesson
		$wpdb->insert( "{$wpdb->prefix}edu_lessons", array(
			'course_id'   => $course_id,
			'module_id'   => 0,
			'title'       => 'Welcome: Course Overview & Mindset',
			'content'     => 'Welcome to the course! In this introductory lesson, we will cover the roadmap to your success and set the right mindset for the journey ahead.',
			'lesson_type' => 'video',
			'video_url'   => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
			'order_index' => 0
		) );

		// Add modules
		$modules = array(
			'Mindset & Foundations' => array(
				array('type' => 'video', 'title' => 'The Entrepreneurial Mindset', 'content' => 'Understand the psychological foundations of business.'),
				array('type' => 'quiz', 'title' => 'Foundations Knowledge Check', 'content' => 'Test your understanding of the core foundations.')
			),
			'Product Development' => array(
				array('type' => 'video', 'title' => 'Building Your MVP', 'content' => 'How to build and iterate on your minimum viable product.'),
				array('type' => 'assignment', 'title' => 'Project: Draft Your Product Specs', 'content' => 'Submit your initial product specifications for review.')
			),
			'Marketing Mastery' => array(
				array('type' => 'video', 'title' => 'Scaling Your Reach', 'content' => 'Advanced marketing strategies for growth.'),
				array('type' => 'pdf', 'title' => 'Marketing Toolkit & Resources', 'content' => 'Download our exclusive marketing templates and resource lists.')
			)
		);

		$m_idx = 0;
		foreach ( $modules as $m_title => $lessons ) {
			$wpdb->insert( "{$wpdb->prefix}edu_modules", array(
				'course_id'   => $course_id,
				'title'       => $m_title,
				'order_index' => $m_idx++
			) );
			$module_id = $wpdb->insert_id;

			foreach ( $lessons as $l_idx => $l_data ) {
				$wpdb->insert( "{$wpdb->prefix}edu_lessons", array(
					'course_id'   => $course_id,
					'module_id'   => $module_id,
					'title'       => $l_data['title'],
					'content'     => $l_data['content'],
					'lesson_type' => $l_data['type'],
					'video_url'   => $l_data['type'] === 'video' ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : '',
					'order_index' => $l_idx
				) );
				$lesson_id = $wpdb->insert_id;

				if ( $l_data['type'] === 'quiz' ) {
					$questions = array(
						array('q' => 'What is the most important trait for a tutor?', 'a' => array('Patience', 'Empathy', 'Expertise', 'All of the above'), 'c' => 3),
						array('q' => 'Should you drip-feed content?', 'a' => array('Yes, always', 'No, never', 'It depends on the course', 'Only for free courses'), 'c' => 2)
					);
					$wpdb->insert( "{$wpdb->prefix}edu_quizzes", array(
						'lesson_id' => $lesson_id,
						'title'     => 'Quiz: ' . $l_data['title'],
						'questions' => json_encode($questions)
					) );
				}

				if ( $l_data['type'] === 'assignment' ) {
					$wpdb->insert( "{$wpdb->prefix}edu_assignments", array(
						'lesson_id'    => $lesson_id,
						'title'        => 'Assignment: ' . $l_data['title'],
						'instructions' => 'Please provide a 2-page document outlining your product features, target audience, and pricing strategy.'
					) );
				}

				if ( $l_data['type'] === 'pdf' ) {
					$wpdb->insert( "{$wpdb->prefix}edu_resources", array(
						'lesson_id' => $lesson_id,
						'title'     => 'Strategic Marketing Plan Template',
						'url'       => 'https://example.com/marketing-template.pdf'
					) );
					$wpdb->insert( "{$wpdb->prefix}edu_resources", array(
						'lesson_id' => $lesson_id,
						'title'     => 'Resource List 2024',
						'url'       => 'https://example.com/resources.pdf'
					) );
				}
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

		// Sample KB Articles
		$wpdb->insert( "{$wpdb->prefix}edu_kb", array(
			'title'    => 'Getting Started with Your Business',
			'content'  => 'Learn the basics of setting up your tutor profile and launch your first course in minutes.',
			'category' => 'setup'
		) );

		$wpdb->insert( "{$wpdb->prefix}edu_kb", array(
			'title'    => 'Accepting Payments via Stripe',
			'content'  => 'Configure your Stripe API keys in the Settings tab to start accepting credit card payments globally.',
			'category' => 'payments'
		) );

		// Sample Promo Assets
		$wpdb->insert( "{$wpdb->prefix}edu_assets", array(
			'title'      => 'Main Sales Email Swipe',
			'content'    => "Subject: You're invited to join our exclusive program!\n\nHi [Name],\n\nI wanted to share this opportunity with you...",
			'asset_type' => 'text'
		) );

		$wpdb->insert( "{$wpdb->prefix}edu_assets", array(
			'title'      => 'Sidebar Banner 300x250',
			'content'    => 'https://via.placeholder.com/300x250.png?text=Join+EdupreneurPro+Today',
			'asset_type' => 'banner'
		) );
	}
}
