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
			__( 'Business Dashboard', 'edupreneur-pro' ),
			__( 'Business Dashboard', 'edupreneur-pro' ),
			'view_edu_reports',
			'edu-dashboard',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'edupreneur-pro',
			__( 'Help & Support', 'edupreneur-pro' ),
			__( 'Help & Support', 'edupreneur-pro' ),
			'read',
			'edu-help',
			array( $this, 'render_help_page' )
		);
	}

	public function render_help_page() {
		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'Help & Support', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Find guides and documentation to help you get the most out of EdupreneurPro.', 'edupreneur-pro' ) . '</p></header>';

		echo '<div class="edu-card"><h3>' . esc_html__( 'Documentation', 'edupreneur-pro' ) . '</h3>';
		echo '<p>' . esc_html__( 'Check out the /docs folder in the plugin directory for the full manual, API references, and security reports.', 'edupreneur-pro' ) . '</p></div>';
		echo '</div>';
	}

	public function render_dashboard() {
		$analytics = new AnalyticsEngine();
		$stats = array( 'gross' => 0, 'net' => 0 );

		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'Tutor Business Dashboard', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Monitor your revenue, student growth, and affiliate performance.', 'edupreneur-pro' ) . '</p></header>';

		echo '<div class="edu-guide-section"><h4>' . esc_html__( 'Getting Started', 'edupreneur-pro' ) . '</h4>';
		echo '<p>' . esc_html__( 'Use this dashboard to keep track of your business health. You can see real-time earnings and course engagement metrics below.', 'edupreneur-pro' ) . '</p></div>';

		echo '<div class="edu-grid">';
		echo '<div class="edu-card">';
		echo '<h3>' . esc_html__( 'Gross Revenue', 'edupreneur-pro' ) . '</h3>';
		echo '<div class="edu-stat-val">$' . number_format( $stats['gross'], 2 ) . '</div>';
		echo '<p>' . esc_html__( 'Total revenue before refunds and commissions.', 'edupreneur-pro' ) . '</p>';
		echo '</div>';

		echo '<div class="edu-card">';
		echo '<h3>' . esc_html__( 'Net Revenue', 'edupreneur-pro' ) . '</h3>';
		echo '<div class="edu-stat-val">$' . number_format( $stats['net'], 2 ) . '</div>';
		echo '<p>' . esc_html__( 'Actual profit after all deductions.', 'edupreneur-pro' ) . '</p>';
		echo '</div>';

		echo '<div class="edu-card" style="grid-column: span 2;">';
		echo '<h3>' . esc_html__( 'Student Growth', 'edupreneur-pro' ) . '</h3>';
		echo '<div style="height: 200px; background: #f9f9f9; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center;">';
		echo '<p>' . esc_html__( 'Student growth chart will appear here.', 'edupreneur-pro' ) . '</p>';
		echo '</div></div>';

		echo '</div></div>';
	}

	public function get_id() {
		return 'dashboard';
	}
}
