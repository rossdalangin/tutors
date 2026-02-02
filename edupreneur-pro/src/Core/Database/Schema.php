<?php
namespace EdupreneurPro\Core\Database;
class Schema {
	public static function get_schema() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix . 'edu_';
		return array(
			"{$prefix}courses" => "CREATE TABLE {$prefix}courses (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				title varchar(255) NOT NULL,
				slug varchar(255) NOT NULL,
				description longtext,
				instructor_id bigint(20) UNSIGNED NOT NULL,
				price decimal(10,2) DEFAULT '0.00',
				status varchar(20) DEFAULT 'draft',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",
			"{$prefix}lessons" => "CREATE TABLE {$prefix}lessons (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				course_id bigint(20) UNSIGNED NOT NULL,
				title varchar(255) NOT NULL,
				content longtext,
				lesson_type varchar(50) DEFAULT 'video',
				order_index int(11) DEFAULT 0,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",
			"{$prefix}enrollments" => "CREATE TABLE {$prefix}enrollments (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				student_id bigint(20) UNSIGNED NOT NULL,
				course_id bigint(20) UNSIGNED NOT NULL,
				status varchar(20) DEFAULT 'active',
				enrolled_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",
			"{$prefix}orders" => "CREATE TABLE {$prefix}orders (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED NOT NULL,
				total_amount decimal(10,2) NOT NULL,
				status varchar(20) DEFAULT 'pending',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",
			"{$prefix}payments" => "CREATE TABLE {$prefix}payments (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				order_id bigint(20) UNSIGNED NOT NULL,
				amount decimal(10,2) NOT NULL,
				status varchar(20),
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",
			"{$prefix}progress" => "CREATE TABLE {$prefix}progress (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				student_id bigint(20) UNSIGNED NOT NULL,
				course_id bigint(20) UNSIGNED NOT NULL,
				lesson_id bigint(20) UNSIGNED NOT NULL,
				completed tinyint(1) DEFAULT 0,
				completed_at datetime DEFAULT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;",
			"{$prefix}affiliates" => "CREATE TABLE {$prefix}affiliates (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED NOT NULL,
				referral_code varchar(50) NOT NULL,
				commission_rate decimal(5,2) DEFAULT '10.00',
				status varchar(20) DEFAULT 'pending',
				PRIMARY KEY  (id)
			) $charset_collate;",
			"{$prefix}commissions" => "CREATE TABLE {$prefix}commissions (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				affiliate_id bigint(20) UNSIGNED NOT NULL,
				order_id bigint(20) UNSIGNED NOT NULL,
				amount decimal(10,2) NOT NULL,
				status varchar(20) DEFAULT 'unpaid',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",
			"{$prefix}community_posts" => "CREATE TABLE {$prefix}community_posts (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				course_id bigint(20) UNSIGNED DEFAULT 0,
				user_id bigint(20) UNSIGNED NOT NULL,
				parent_id bigint(20) UNSIGNED DEFAULT 0,
				content text NOT NULL,
				is_pinned tinyint(1) DEFAULT 0,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}quizzes" => "CREATE TABLE {$prefix}quizzes (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				lesson_id bigint(20) UNSIGNED NOT NULL,
				title varchar(255) NOT NULL,
				questions longtext,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}assignments" => "CREATE TABLE {$prefix}assignments (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				lesson_id bigint(20) UNSIGNED NOT NULL,
				title varchar(255) NOT NULL,
				instructions text,
				PRIMARY KEY  (id)
			) $charset_collate;",
		);
	}
}
