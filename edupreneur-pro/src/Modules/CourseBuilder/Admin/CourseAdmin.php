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
		$container = \EdupreneurPro::instance()->container;

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

			echo '<div class="edu-card"><h3>' . esc_html__( 'Quick Start Tools', 'edupreneur-pro' ) . '</h3>';
			echo '<p>' . esc_html__( 'Need inspiration? Populate your business with realistic sample data to see how the system handles courses, students, and payments.', 'edupreneur-pro' ) . '</p>';
			echo '<button id="edu-load-samples" class="edu-btn edu-btn-secondary">' . esc_html__( 'Load Sample Data', 'edupreneur-pro' ) . '</button>';
			echo '<button id="edu-reset-data" class="edu-btn-link" style="color:#d63638; margin-left:10px;">' . esc_html__( 'Reset All Data', 'edupreneur-pro' ) . '</button>';
			echo '<div id="sample-status" style="margin-top:10px;"></div></div>';

			echo '<script>
				jQuery(document).on("click", "#edu-load-samples", function() {
					const btn = jQuery(this);
					const status = jQuery("#sample-status");
					btn.prop("disabled", true).text("Loading...");
					jQuery.post(eduApi.root + "edupreneur/v1/debug/sample-data", { _wpnonce: eduApi.nonce }, function(res) {
						status.html("<p style=\'color:green; font-weight:bold;\'>" + res.message + "</p>");
						btn.text("Data Loaded").prop("disabled", true);
						setTimeout(() => location.reload(), 1500);
					}).fail(function(err) {
						status.html("<p style=\'color:red;\'>Error: " + (err.responseJSON ? err.responseJSON.message : "Request failed") + "</p>");
						btn.prop("disabled", false).text("Load Sample Data");
					});
				});
				jQuery(document).on("click", "#edu-reset-data", function() {
					if(!confirm("Are you sure? This will delete all courses, lessons, and student progress!")) return;
					const status = jQuery("#sample-status");
					jQuery.post(eduApi.root + "edupreneur/v1/debug/reset", { _wpnonce: eduApi.nonce }, function(res) {
						status.html("<p style=\'color:orange;\'>" + res.message + "</p>");
						setTimeout(() => location.reload(), 1000);
					});
				});
			</script>';

			echo '</div></div>';
		} elseif ( current_user_can( 'view_edu_affiliate_dashboard' ) ) {
			$aff_module = $container->get( 'module_affiliate' );
			if ( $aff_module ) {
				$aff_module->render_affiliate_dashboard();
			}
		} elseif ( current_user_can( 'view_edu_courses' ) ) {
			$dash_module = $container->get( 'module_dashboard' );
			if ( $dash_module ) {
				$dash_module->render_my_courses_page();
			}
		} else {
			$dash_module = $container->get( 'module_dashboard' );
			if ( $dash_module ) {
				$dash_module->render_help_page();
			}
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
		echo '</div>';

		echo '<div class="edu-card" style="margin-top:40px; border-top: 3px solid #eee;">';
		echo '<h3>' . esc_html__( 'System Diagnostics', 'edupreneur-pro' ) . '</h3>';
		echo '<p>' . esc_html__( 'If your lessons are not appearing in the builder, use the tool below to verify if they exist in the database.', 'edupreneur-pro' ) . '</p>';
		echo '<button id="edu-diagnostic-btn" class="edu-btn edu-btn-secondary">' . esc_html__( 'Scan Database for Lessons', 'edupreneur-pro' ) . '</button>';
		echo '<div id="edu-diagnostic-results" style="margin-top:20px;"></div>';
		echo '</div>';

		echo '<script>
			jQuery(document).on("click", "#edu-diagnostic-btn", function() {
				const results = jQuery("#edu-diagnostic-results");
				results.html("<p>Scanning...</p>");
				jQuery.get(eduApi.root + "edupreneur/v1/lessons/diagnostic", { _wpnonce: eduApi.nonce }, function(res) {
					if(!res.length) {
						results.html("<p>No lessons found in database.</p>");
						return;
					}
					let html = "<table class=\'wp-list-table widefat fixed striped\'><thead><tr><th>ID</th><th>Title</th><th>Course ID</th><th>Module ID</th></tr></thead><tbody>";
					res.forEach(l => {
						html += "<tr><td>"+l.id+"</td><td>"+l.title+"</td><td>"+l.course_id+"</td><td>"+l.module_id+"</td></tr>";
					});
					html += "</tbody></table>";
					results.html(html);
				}).fail(function() {
					results.html("<p style=\'color:red;\'>Diagnostic failed. Check permissions.</p>");
				});
			});
		</script>';

		echo '</div>';
	}
}
