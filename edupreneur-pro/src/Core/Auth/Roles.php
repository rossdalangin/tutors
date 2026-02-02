<?php
namespace EdupreneurPro\Core\Auth;
class Roles {
	public static function register() {
		add_role( 'tutor', 'Tutor', array( 'read' => true, 'manage_edu_courses' => true, 'manage_edu_lessons' => true, 'view_edu_reports' => true ) );
		add_role( 'student', 'Student', array( 'read' => true, 'view_edu_courses' => true ) );
		add_role( 'affiliate', 'Affiliate', array( 'read' => true ) );
	}
}
