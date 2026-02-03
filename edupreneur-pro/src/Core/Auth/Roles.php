<?php
namespace EdupreneurPro\Core\Auth;
class Roles {
	public static function register() {
		self::ensure_roles_exist();
		self::ensure_all_caps();
	}

	/**
	 * Ensure custom roles exist in the system.
	 */
	public static function ensure_roles_exist() {
		if ( ! get_role( 'tutor' ) ) {
			add_role( 'tutor', 'Tutor', array( 'read' => true ) );
		}
		if ( ! get_role( 'student' ) ) {
			add_role( 'student', 'Student', array( 'read' => true ) );
		}
		if ( ! get_role( 'affiliate' ) ) {
			add_role( 'affiliate', 'Affiliate', array( 'read' => true ) );
		}
	}

	/**
	 * Grant capabilities to administrator.
	 */
	public static function grant_admin_caps() {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$caps = array(
				'manage_edu_courses',
				'manage_edu_lessons',
				'view_edu_reports',
				'view_edu_courses',
				'view_edu_affiliate_dashboard',
				'view_edu_community',
				'view_edu_orders'
			);
			foreach ( $caps as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	/**
	 * Ensure all roles have their expected capabilities.
	 */
	public static function ensure_all_caps() {
		self::grant_admin_caps();

		$tutor = get_role( 'tutor' );
		if ( $tutor ) {
			$tutor->add_cap( 'manage_edu_courses' );
			$tutor->add_cap( 'manage_edu_lessons' );
			$tutor->add_cap( 'view_edu_reports' );
			$tutor->add_cap( 'view_edu_community' );
		}

		$student = get_role( 'student' );
		if ( $student ) {
			$student->add_cap( 'view_edu_courses' );
			$student->add_cap( 'view_edu_community' );
			$student->add_cap( 'view_edu_orders' );
		}

		$affiliate = get_role( 'affiliate' );
		if ( $affiliate ) {
			$affiliate->add_cap( 'view_edu_affiliate_dashboard' );
		}
	}
}
