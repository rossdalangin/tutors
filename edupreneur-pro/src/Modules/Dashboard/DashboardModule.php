<?php

namespace EdupreneurPro\Modules\Dashboard;

use EdupreneurPro\Core\Modules\ModuleInterface;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\Dashboard\Services\AnalyticsEngine;

class DashboardModule implements ModuleInterface {

	private $container;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function init() {
		add_action( 'admin_menu', array( $this, 'register_dashboard_menu' ), 20 );
	}

	public function register_dashboard_menu() {
		add_submenu_page(
			'edupreneur-pro',
			__( 'Business Insights', 'edupreneur-pro' ),
			__( 'Business Dashboard', 'edupreneur-pro' ),
			'view_edu_reports',
			'edu-dashboard',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'edupreneur-pro',
			__( 'Learning Guides', 'edupreneur-pro' ),
			__( 'Help & Support', 'edupreneur-pro' ),
			'read',
			'edu-help',
			array( $this, 'render_help_page' )
		);

		add_submenu_page(
			'edupreneur-pro',
			__( 'System Settings', 'edupreneur-pro' ),
			__( 'Settings & Tools', 'edupreneur-pro' ),
			'manage_options',
			'edu-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function render_settings_page() {
		if ( isset( $_POST['edu_action'] ) && check_admin_referer( 'edu_system_action' ) ) {
			if ( $_POST['edu_action'] === 'clear_db' ) {
				\EdupreneurPro\Core\SystemService::clear_database();
				echo '<div class="updated"><p>Database cleared successfully.</p></div>';
			} elseif ( $_POST['edu_action'] === 'sample_data' ) {
				\EdupreneurPro\Core\SystemService::add_sample_data();
				echo '<div class="updated"><p>Sample data injected successfully.</p></div>';
			}
		}

		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'System Settings & Tools', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Manage your platform defaults and use development tools.', 'edupreneur-pro' ) . '</p></header>';

		echo '<div class="edu-grid">';
		echo '<div class="edu-card"><h3>' . esc_html__( 'Platform Maintenance', 'edupreneur-pro' ) . '</h3>';
		echo '<p>' . esc_html__( 'Use these tools to manage your database state. Warning: Clearing the database is irreversible.', 'edupreneur-pro' ) . '</p>';

		echo '<form method="post" style="margin-top:20px; display:flex; gap:10px;">';
		wp_nonce_field( 'edu_system_action' );
		echo '<button type="submit" name="edu_action" value="sample_data" class="edu-btn">' . esc_html__( 'Load Sample Data', 'edupreneur-pro' ) . '</button>';
		echo '<button type="submit" name="edu_action" value="clear_db" class="edu-btn" style="background:#dc3545;" onclick="return confirm(\'Are you sure? This will delete all courses and students.\')">' . esc_html__( 'Reset Database', 'edupreneur-pro' ) . '</button>';
		echo '</form></div>';

		echo '<div class="edu-card"><h3>' . esc_html__( 'General Configuration', 'edupreneur-pro' ) . '</h3>';
		echo '<p>' . esc_html__( 'Platform settings like currency, timezone, and student registration defaults will be available here in the next update.', 'edupreneur-pro' ) . '</p></div>';
		echo '</div></div>';
	}

	public function render_help_page() {
		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'EdupreneurPro Knowledge Base', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Master every feature of your education business system. From setup to scale, we have you covered.', 'edupreneur-pro' ) . '</p></header>';

		echo '<div class="edu-grid">';
		echo '<div class="edu-card"><h3>' . esc_html__( 'Getting Started Guide', 'edupreneur-pro' ) . '</h3>';
		echo '<p>' . esc_html__( 'Learn how to configure your payment gateways, set up your instructor profile, and launch your first course in under 15 minutes.', 'edupreneur-pro' ) . '</p></div>';

		echo '<div class="edu-card"><h3>' . esc_html__( 'Maximizing Revenue', 'edupreneur-pro' ) . '</h3>';
		echo '<p>' . esc_html__( 'Discover how to use the built-in affiliate engine and subscription models to create sustainable, recurring income.', 'edupreneur-pro' ) . '</p></div>';

		echo '<div class="edu-card"><h3>' . esc_html__( 'Developer API Reference', 'edupreneur-pro' ) . '</h3>';
		echo '<p>' . esc_html__( 'Extend EdupreneurPro with our robust REST API. Perfect for building custom mobile apps or third-party integrations.', 'edupreneur-pro' ) . '</p></div>';
		echo '</div>';

		echo '<div class="edu-guide-section"><h4>' . esc_html__( 'Need Technical Assistance?', 'edupreneur-pro' ) . '</h4>';
		echo '<p>' . esc_html__( 'Detailed documentation files (API.md, Manual.md) are available in the plugin directory for advanced users and developers.', 'edupreneur-pro' ) . '</p></div>';
		echo '</div>';
	}

	public function render_dashboard() {
		$analytics = new AnalyticsEngine();
		$stats = $analytics->get_revenue_stats();

		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'Business Performance Overview', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Real-time analytics to help you make data-driven decisions for your education business.', 'edupreneur-pro' ) . '</p></header>';

		echo '<div class="edu-guide-section"><h4>' . esc_html__( 'Understanding Your Metrics', 'edupreneur-pro' ) . '</h4>';
		echo '<p>' . esc_html__( 'Use the Gross Revenue to see total sales volume, and Net Revenue to understand your actual profitability after refunds and costs.', 'edupreneur-pro' ) . '</p></div>';

		echo '<div class="edu-grid">';
		echo '<div class="edu-card">';
		echo '<h3>' . esc_html__( 'Gross Sales Volume', 'edupreneur-pro' ) . '</h3>';
		echo '<div class="edu-stat-val">$' . number_format( $stats['gross'], 2 ) . '</div>';
		echo '<p>' . esc_html__( 'Total transaction value before any deductions.', 'edupreneur-pro' ) . '</p>';
		echo '</div>';

		echo '<div class="edu-card">';
		echo '<h3>' . esc_html__( 'Net Business Profit', 'edupreneur-pro' ) . '</h3>';
		echo '<div class="edu-stat-val">$' . number_format( $stats['net'], 2 ) . '</div>';
		echo '<p>' . esc_html__( 'Actual revenue retained by your business.', 'edupreneur-pro' ) . '</p>';
		echo '</div>';

		echo '<div class="edu-card" style="grid-column: span 2;">';
		echo '<h3>' . esc_html__( 'Student Enrollment Growth', 'edupreneur-pro' ) . '</h3>';
		echo '<div style="height: 200px; background: #f9f9f9; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center;">';
		echo '<p>' . esc_html__( 'Visualizing your student growth trends. Check back as you enroll more learners!', 'edupreneur-pro' ) . '</p>';
		echo '</div></div>';

		echo '</div></div>';
	}

	public function get_id() {
		return 'dashboard';
	}
}
