<?php
namespace EdupreneurPro\Modules\Affiliate;

use EdupreneurPro\Core\Modules\ModuleInterface;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\Affiliate\Services\AffiliateManager;
use EdupreneurPro\Modules\Affiliate\Controllers\AffiliateController;

class AffiliateModule implements ModuleInterface {
	private $container;
	public function __construct( Container $container ) { $this->container = $container; }
	public function init() {
		$this->container->set( 'affiliate_manager', new AffiliateManager() );

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'admin_menu', array( $this, 'register_affiliate_menu' ) );
	}

	public function register_affiliate_menu() {
		add_submenu_page(
			'edupreneur-pro',
			__( 'Affiliate Partner Dashboard', 'edupreneur-pro' ),
			__( 'Affiliate Dashboard', 'edupreneur-pro' ),
			'view_edu_affiliate_dashboard',
			'edu-affiliate-dashboard',
			array( $this, 'render_affiliate_dashboard' )
		);

		add_submenu_page(
			'edupreneur-pro',
			__( 'Affiliate Promo Materials', 'edupreneur-pro' ),
			__( 'Promo Assets', 'edupreneur-pro' ),
			'view_edu_affiliate_dashboard',
			'edu-affiliate-assets',
			array( $this, 'render_affiliate_assets' )
		);
	}

	public function render_affiliate_assets() {
		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'Promotional Assets', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'High-converting banners and copy to help you sell more.', 'edupreneur-pro' ) . '</p></header>';

		echo '<div class="edu-grid">';
		echo '<div class="edu-card"><h3>' . esc_html__( 'Email Swipe 1', 'edupreneur-pro' ) . '</h3>';
		echo '<pre style="background:#f4f4f4; padding:15px; white-space: pre-wrap;">' . esc_html__( "Subject: Master Digital Entrepreneurship Today!\n\nHey [Name],\n\nI just found this amazing course...", 'edupreneur-pro' ) . '</pre></div>';

		echo '<div class="edu-card"><h3>' . esc_html__( 'Social Media Graphic', 'edupreneur-pro' ) . '</h3>';
		echo '<div style="width:100%; height:150px; background:#ddd; display:flex; align-items:center; justify-content:center; color:#666;">Banner Placeholder 300x250</div></div>';
		echo '</div></div>';
	}

	public function render_affiliate_dashboard() {
		$dashboard = new \EdupreneurPro\Modules\Affiliate\Services\AffiliateDashboard();
		$stats = $dashboard->get_stats( get_current_user_id() );

		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'Partner Dashboard', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Track your referrals, earnings, and promotional links.', 'edupreneur-pro' ) . '</p></header>';

		if ( empty( $stats ) ) {
			echo '<div class="edu-card"><p>' . esc_html__( 'You are not yet registered as an affiliate partner.', 'edupreneur-pro' ) . '</p>';
			echo '<button id="register-affiliate-btn" class="edu-btn">' . esc_html__( 'Join Affiliate Program', 'edupreneur-pro' ) . '</button></div>';
			echo '<script>jQuery("#register-affiliate-btn").click(function(){ jQuery.post(eduApi.root + "edupreneur/v1/affiliates/register", { _wpnonce: eduApi.nonce }, function(){ location.reload(); }); });</script>';
			return;
		}

		echo '<div class="edu-grid">';
		echo '<div class="edu-card"><h3>' . esc_html__( 'Your Referral Link', 'edupreneur-pro' ) . '</h3>';
		echo '<input type="text" value="' . esc_attr( $stats['referral_link'] ) . '" class="edu-btn-block" readonly onclick="this.select();">';
		echo '<p class="edu-caption">' . esc_html__( 'Share this link to earn commissions on every sale.', 'edupreneur-pro' ) . '</p></div>';

		echo '<div class="edu-card"><h3>' . esc_html__( 'Total Earnings', 'edupreneur-pro' ) . '</h3>';
		echo '<div class="edu-stat-val">$' . number_format( $stats['total_earnings'], 2 ) . '</div></div>';

		echo '<div class="edu-card"><h3>' . esc_html__( 'Conversions', 'edupreneur-pro' ) . '</h3>';
		echo '<div class="edu-stat-val">' . intval( $stats['conversions'] ) . '</div></div>';
		echo '</div></div>';
	}
	public function register_routes() {
		$controller = new AffiliateController();
		$controller->register_routes();
	}
	public function get_id() { return 'affiliate'; }
}
