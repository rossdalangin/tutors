<?php
namespace EdupreneurPro\Core\Auth;
class Roles {
	public static function register() {
		add_role( 'tutor', 'Tutor', array( 'read' => true, 'manage_edu_courses' => true, 'manage_edu_lessons' => true, 'view_edu_reports' => true ) );
		add_role( 'student', 'Student', array( 'read' => true, 'view_edu_courses' => true ) );
		add_role( 'affiliate', 'Affiliate', array( 'read' => true ) );

		// Grant capabilities to administrator.
		self::grant_admin_caps();
	}

	/**
	 * Grant capabilities to administrator.
	 */
	public static function grant_admin_caps() {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( 'manage_edu_courses' );
			$admin->add_cap( 'manage_edu_lessons' );
			$admin->add_cap( 'view_edu_reports' );
		}
	}
}
