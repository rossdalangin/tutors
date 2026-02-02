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
	public function render_dashboard() {
		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'Welcome to EdupreneurPro', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Operate your full-scale education business from a single dashboard.', 'edupreneur-pro' ) . '</p></header>';

		echo '<div class="edu-grid">';
		echo '<div class="edu-card"><h3>' . esc_html__( 'Course Builder', 'edupreneur-pro' ) . '</h3>';
		echo '<p>' . esc_html__( 'Create and manage your courses, modules, and lessons with ease.', 'edupreneur-pro' ) . '</p>';
		echo '<a href="' . admin_url( 'admin.php?page=edu-courses' ) . '" class="edu-btn">' . esc_html__( 'Manage Courses', 'edupreneur-pro' ) . '</a></div>';

		echo '<div class="edu-card"><h3>' . esc_html__( 'Student Engagement', 'edupreneur-pro' ) . '</h3>';
		echo '<p>' . esc_html__( 'Interact with your community and track student progress.', 'edupreneur-pro' ) . '</p></div>';
		echo '</div></div>';
	}
	public function render_courses_page() {
		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'Manage Courses', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Organize your curriculum using the drag-and-drop course builder.', 'edupreneur-pro' ) . '</p></header>';

		echo '<div class="edu-guide-section"><h4>' . esc_html__( 'Quick Tip', 'edupreneur-pro' ) . '</h4>';
		echo '<p>' . esc_html__( 'Start by creating a course, then add modules and lessons to build your curriculum.', 'edupreneur-pro' ) . '</p></div>';

		echo '<div id="edu-course-builder-root" class="edu-card">';
		echo '<p>' . esc_html__( 'Course builder loading...', 'edupreneur-pro' ) . '</p>';
		echo '</div></div>';
	}
}
