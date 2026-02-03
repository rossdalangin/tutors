<?php
namespace EdupreneurPro\Modules\CourseBuilder\Admin;

class CourseAdmin {
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}
	public function register_menu() {
		add_menu_page( 'EdupreneurPro', 'EdupreneurPro', 'read', 'edupreneur-pro', array( $this, 'render_dashboard' ), 'dashicons-education', 25 );
		add_submenu_page( 'edupreneur-pro', 'Courses', 'Manage Courses', 'manage_edu_courses', 'edu-courses', array( $this, 'render_courses_page' ) );
	}
	public function render_dashboard() {
		if ( current_user_can( 'manage_edu_courses' ) ) {
			echo '<div class="edu-admin-wrap">';
			echo '<header class="edu-header"><h1>' . esc_html__( 'EdupreneurPro Command Center', 'edupreneur-pro' ) . '</h1>';
			echo '<p>' . esc_html__( 'Empowering you to build, manage, and scale your online education business with ease. Start by exploring your tools below.', 'edupreneur-pro' ) . '</p></header>';

			echo '<div class="edu-grid">';
			echo '<div class="edu-card"><h3>' . esc_html__( 'Course Builder Engine', 'edupreneur-pro' ) . '</h3>';
			echo '<p>' . esc_html__( 'The heart of your business. Create rich, multi-layered courses with modules and lessons designed to deliver high-impact learning experiences.', 'edupreneur-pro' ) . '</p>';
			echo '<a href="' . admin_url( 'admin.php?page=edu-courses' ) . '" class="edu-btn">' . esc_html__( 'Launch Course Builder', 'edupreneur-pro' ) . '</a></div>';

			echo '<div class="edu-card"><h3>' . esc_html__( 'Student Engagement Hub', 'edupreneur-pro' ) . '</h3>';
			echo '<p>' . esc_html__( 'Monitor student progress in real-time and foster a thriving community. Engaged students lead to better completion rates and higher revenue.', 'edupreneur-pro' ) . '</p></div>';

			echo '<div class="edu-card"><h3>' . esc_html__( 'Monetization & Sales', 'edupreneur-pro' ) . '</h3>';
			echo '<p>' . esc_html__( 'Track your revenue from Stripe, PayPal, and GCash. Manage your affiliate partners and optimize your sales funnel from a single view.', 'edupreneur-pro' ) . '</p>';
			echo '<a href="' . admin_url( 'admin.php?page=edu-dashboard' ) . '" class="edu-btn">' . esc_html__( 'View Sales Reports', 'edupreneur-pro' ) . '</a></div>';
			echo '</div></div>';
		} elseif ( current_user_can( 'view_edu_affiliate_dashboard' ) ) {
			$aff_module = new \EdupreneurPro\Modules\Affiliate\AffiliateModule( \EdupreneurPro::instance()->container );
			$aff_module->render_affiliate_dashboard();
		} elseif ( current_user_can( 'view_edu_courses' ) ) {
			$dash_module = new \EdupreneurPro\Modules\Dashboard\DashboardModule( \EdupreneurPro::instance()->container );
			$dash_module->render_my_courses_page();
		} else {
			$dash_module = new \EdupreneurPro\Modules\Dashboard\DashboardModule( \EdupreneurPro::instance()->container );
			$dash_module->render_help_page();
		}
	}
	public function render_courses_page() {
		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'Curriculum Architect', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Define your educational path. Here you can create courses and structure them into logical modules and lessons.', 'edupreneur-pro' ) . '</p></header>';

		echo '<div class="edu-guide-section"><h4>' . esc_html__( 'How to Build Your Course', 'edupreneur-pro' ) . '</h4>';
		echo '<p>' . esc_html__( '1. Click "Create New Course" to start a new curriculum. 2. Use the "Add Lesson" button on any course card to begin adding educational content. 3. Remember to include descriptions to help your students understand what they will learn.', 'edupreneur-pro' ) . '</p></div>';

		echo '<div id="edu-course-builder-root" class="edu-card">';
		echo '<p>' . esc_html__( 'Building your curriculum interface...', 'edupreneur-pro' ) . '</p>';
		echo '</div></div>';
	}
}
