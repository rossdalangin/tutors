<?php
namespace EdupreneurPro\Core\Database;
class Schema {
	public static function get_schema() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix . 'edu_';
		return array(
			"{$prefix}categories" => "CREATE TABLE {$prefix}categories (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				slug varchar(255) NOT NULL,
				description text,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}courses" => "CREATE TABLE {$prefix}courses (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				title varchar(255) NOT NULL,
				slug varchar(255) NOT NULL,
				category varchar(100) DEFAULT 'General',
				category_id bigint(20) UNSIGNED DEFAULT 0,
				description longtext,
				instructor_id bigint(20) UNSIGNED NOT NULL,
				course_type varchar(20) DEFAULT 'course',
				price decimal(10,2) DEFAULT '0.00',
				pricing_model varchar(20) DEFAULT 'one-time',
				billing_period varchar(20) DEFAULT '',
				trial_days int(11) DEFAULT 0,
				installment_count int(11) DEFAULT 0,
				installment_interval varchar(20) DEFAULT '',
				status varchar(20) DEFAULT 'draft',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",
			"{$prefix}modules" => "CREATE TABLE {$prefix}modules (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				course_id bigint(20) UNSIGNED NOT NULL,
				title varchar(255) NOT NULL,
				order_index int(11) DEFAULT 0,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}bundle_items" => "CREATE TABLE {$prefix}bundle_items (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				bundle_id bigint(20) UNSIGNED NOT NULL,
				course_id bigint(20) UNSIGNED NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}payouts" => "CREATE TABLE {$prefix}payouts (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				affiliate_id bigint(20) UNSIGNED NOT NULL,
				amount decimal(10,2) NOT NULL,
				status varchar(20) DEFAULT 'pending',
				method varchar(50),
				details text,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}lessons" => "CREATE TABLE {$prefix}lessons (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				course_id bigint(20) UNSIGNED NOT NULL,
				module_id bigint(20) UNSIGNED DEFAULT 0,
				title varchar(255) NOT NULL,
				content longtext,
				lesson_type varchar(50) DEFAULT 'video',
				video_url varchar(255) DEFAULT '',
				drip_days int(11) DEFAULT 0,
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
				ip_address varchar(45) DEFAULT '',
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
				recipient_id bigint(20) UNSIGNED DEFAULT 0,
				parent_id bigint(20) UNSIGNED DEFAULT 0,
				content text NOT NULL,
				is_pinned tinyint(1) DEFAULT 0,
				is_locked tinyint(1) DEFAULT 0,
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

			"{$prefix}resources" => "CREATE TABLE {$prefix}resources (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				lesson_id bigint(20) UNSIGNED NOT NULL,
				title varchar(255) NOT NULL,
				url varchar(255) NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}kb" => "CREATE TABLE {$prefix}kb (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				title varchar(255) NOT NULL,
				content longtext NOT NULL,
				category varchar(50) DEFAULT 'general',
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}assets" => "CREATE TABLE {$prefix}assets (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				title varchar(255) NOT NULL,
				content text NOT NULL,
				asset_type varchar(50) DEFAULT 'text',
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}products" => "CREATE TABLE {$prefix}products (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				title varchar(255) NOT NULL,
				price decimal(10,2) DEFAULT '0.00',
				file_url varchar(255) DEFAULT '',
				download_limit int(11) DEFAULT 0,
				expiry_days int(11) DEFAULT 0,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}coupons" => "CREATE TABLE {$prefix}coupons (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				code varchar(50) NOT NULL,
				discount_type varchar(20) DEFAULT 'percentage',
				discount_amount decimal(10,2) NOT NULL,
				expiry_date datetime DEFAULT NULL,
				usage_limit int(11) DEFAULT 0,
				usage_count int(11) DEFAULT 0,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}audit_log" => "CREATE TABLE {$prefix}audit_log (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED NOT NULL,
				action varchar(255) NOT NULL,
				object_type varchar(50),
				object_id bigint(20) UNSIGNED,
				details text,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}subscriptions" => "CREATE TABLE {$prefix}subscriptions (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED NOT NULL,
				course_id bigint(20) UNSIGNED NOT NULL,
				status varchar(20) DEFAULT 'active',
				billing_period varchar(20) DEFAULT 'monthly',
				next_billing_at datetime,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}notes" => "CREATE TABLE {$prefix}notes (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED NOT NULL,
				lesson_id bigint(20) UNSIGNED NOT NULL,
				content text NOT NULL,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",

			"{$prefix}license_keys" => "CREATE TABLE {$prefix}license_keys (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				product_id bigint(20) UNSIGNED NOT NULL,
				user_id bigint(20) UNSIGNED NOT NULL,
				license_key varchar(100) NOT NULL,
				status varchar(20) DEFAULT 'active',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",
		);
	}
}
