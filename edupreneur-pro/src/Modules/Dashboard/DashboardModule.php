<?php

namespace EdupreneurPro\Modules\Dashboard;

use EdupreneurPro\Core\Modules\ModuleInterface;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\Dashboard\Services\AnalyticsEngine;

/**
 * Dashboard Module Class
 */
class DashboardModule implements ModuleInterface {

	/**
	 * Container instance.
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Initialize the module.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_dashboard_menu' ), 20 );
	}

	/**
	 * Register dashboard menu.
	 */
	public function register_dashboard_menu() {
		add_submenu_page(
			'edupreneur-pro',
			__( 'Business Dashboard', 'edupreneur-pro' ),
			__( 'Business Dashboard', 'edupreneur-pro' ),
			'view_edu_reports',
			'edu-dashboard',
			array( $this, 'render_dashboard' )
		);
	}

	/**
	 * Render the dashboard.
	 */
	public function render_dashboard() {
		$analytics = new AnalyticsEngine();
		$stats = $analytics->get_revenue_stats();

		echo '<div class="wrap"><h1>' . esc_html__( 'Tutor Business Dashboard', 'edupreneur-pro' ) . '</h1>';
		echo '<div class="edu-stats-grid" style="display: flex; gap: 20px; margin-top: 20px;">';
		echo '<div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; flex: 1;">';
		echo '<h3>' . esc_html__( 'Gross Revenue', 'edupreneur-pro' ) . '</h3>';
		echo '<p style="font-size: 24px;">$' . number_format( $stats['gross'], 2 ) . '</p>';
		echo '</div>';
		echo '<div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; flex: 1;">';
		echo '<h3>' . esc_html__( 'Net Revenue', 'edupreneur-pro' ) . '</h3>';
		echo '<p style="font-size: 24px;">$' . number_format( $stats['net'], 2 ) . '</p>';
		echo '</div>';
		echo '</div></div>';
	}

	/**
	 * Get the module identifier.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'dashboard';
	}
}
