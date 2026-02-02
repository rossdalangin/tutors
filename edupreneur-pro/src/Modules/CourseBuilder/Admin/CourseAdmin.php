<?php
namespace EdupreneurPro\Modules\CourseBuilder\Admin;

class CourseAdmin {
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}
	public function register_menu() {
		add_menu_page( 'EdupreneurPro', 'EdupreneurPro', 'manage_edu_courses', 'edupreneur-pro', array( $this, 'render_dashboard' ), 'dashicons-education', 25 );
		add_submenu_page( 'edupreneur-pro', 'Courses', 'Courses', 'manage_edu_courses', 'edu-courses', array( $this, 'render_courses_page' ) );
	}
	public function render_dashboard() { echo '<div class="wrap"><h1>EdupreneurPro Dashboard</h1></div>'; }
	public function render_courses_page() { echo '<div class="wrap"><h1>Manage Courses</h1><div id="edu-course-builder-root"></div></div>'; }
}
