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

		add_submenu_page(
			'edupreneur-pro',
			__( 'Manage Promo Assets', 'edupreneur-pro' ),
			__( 'Manage Assets', 'edupreneur-pro' ),
			'manage_options',
			'edu-asset-mgmt',
			array( $this, 'render_asset_mgmt_page' )
		);
	}

	public function render_asset_mgmt_page() {
		global $wpdb;
		if ( isset( $_POST['edu_asset_action'] ) ) {
			check_admin_referer( 'edu_asset_action' );
			if ( $_POST['edu_asset_action'] === 'save' ) {
				$data = array( 'title' => sanitize_text_field( $_POST['title'] ), 'content' => sanitize_textarea_field( $_POST['content'] ), 'asset_type' => sanitize_text_field( $_POST['asset_type'] ) );
				if ( ! empty( $_POST['asset_id'] ) ) {
					$wpdb->update( "{$wpdb->prefix}edu_assets", $data, array( 'id' => intval( $_POST['asset_id'] ) ) );
				} else {
					$wpdb->insert( "{$wpdb->prefix}edu_assets", $data );
				}
			} elseif ( $_POST['edu_asset_action'] === 'delete' ) {
				$wpdb->delete( "{$wpdb->prefix}edu_assets", array( 'id' => intval( $_POST['asset_id'] ) ) );
			}
			echo '<div class="updated"><p>Asset updated.</p></div>';
		}
		$items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_assets" );
		echo '<div class="edu-admin-wrap"><h1>Manage Promo Assets</h1>';
		echo '<form method="post" class="edu-card" style="margin-bottom:20px;">';
		wp_nonce_field( 'edu_asset_action' );
		echo '<h3>Add/Edit Asset</h3>';
		echo '<input type="hidden" name="asset_id" id="asset_id">';
		echo '<div class="edu-form-group"><label>Title</label><input type="text" name="title" id="asset_title" required>';
		echo '<p class="edu-field-caption">' . esc_html__( 'Descriptive name for this promotional tool.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-form-group"><label>Type</label><select name="asset_type" id="asset_type"><option value="text">Email Swipe</option><option value="banner">Banner URL</option></select>';
		echo '<p class="edu-field-caption">' . esc_html__( 'Choose whether this is a text-based email swipe or a graphical banner link.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-form-group"><label>Content/URL</label><textarea name="content" id="asset_content" rows="5" required></textarea>';
		echo '<p class="edu-field-caption">' . esc_html__( 'The actual promotional text or the full image URL for the banner.', 'edupreneur-pro' ) . '</p></div>';
		echo '<button type="submit" name="edu_asset_action" value="save" class="edu-btn">Save Asset</button></form>';

		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Title</th><th>Type</th><th>Actions</th></tr></thead><tbody>';
		foreach ( $items as $item ) {
			echo "<tr><td>" . esc_html( $item->title ) . "</td><td>" . esc_html( $item->asset_type ) . "</td><td>";
			$json_data = esc_attr( json_encode( $item ) );
			echo "<button type='button' class='edu-btn' onclick='eduEditAsset({$json_data})'>Edit</button> ";
			echo "<form method='post' style='display:inline;'>";
			wp_nonce_field( 'edu_asset_action' );
			echo "<input type='hidden' name='asset_id' value='{$item->id}'>";
			echo "<button type='submit' name='edu_asset_action' value='delete' class='edu-btn' style='background:#dc3545;' onclick='return confirm(\"Delete asset?\")'>Delete</button></form></td></tr>";
		}
		echo '</tbody></table></div>';
	}

	public function render_affiliate_assets() {
		global $wpdb;
		$items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_assets" );
		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'Promotional Assets', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'High-converting banners and copy to help you sell more.', 'edupreneur-pro' ) . '</p></header>';

		if ( empty( $items ) ) {
			echo '<p>No promotional materials available yet.</p>';
		} else {
			echo '<div class="edu-grid">';
			foreach ( $items as $item ) {
				echo '<div class="edu-card"><h3>' . esc_html( $item->title ) . '</h3>';
				if ( $item->asset_type === 'banner' ) {
					echo '<img src="' . esc_url( $item->content ) . '" style="max-width:100%; height:auto;">';
				} else {
					echo '<pre style="background:#f4f4f4; padding:15px; white-space: pre-wrap;">' . esc_html( $item->content ) . '</pre>';
				}
				echo '</div>';
			}
			echo '</div>';
		}
		echo '</div>';
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

		echo '<div class="edu-card"><h3>' . esc_html__( 'Unpaid Balance', 'edupreneur-pro' ) . '</h3>';
		echo '<div class="edu-stat-val">$' . number_format( $stats['unpaid_balance'], 2 ) . '</div>';
		if ( $stats['payout_ready'] ) {
			echo '<span class="tag tag-success">' . __( 'Payout Ready', 'edupreneur-pro' ) . '</span>';
		} else {
			echo '<span class="tag">' . sprintf( __( 'Min. Payout: $%s', 'edupreneur-pro' ), number_format($stats['threshold'], 2) ) . '</span>';
		}
		echo '</div>';

		echo '<div class="edu-card"><h3>' . esc_html__( 'Total Earnings', 'edupreneur-pro' ) . '</h3>';
		echo '<div class="edu-stat-val">$' . number_format( $stats['total_earnings'], 2 ) . '</div></div>';

		echo '<div class="edu-card"><h3>' . esc_html__( 'Conversions', 'edupreneur-pro' ) . '</h3>';
		echo '<div class="edu-stat-val">' . intval( $stats['conversions'] ) . '</div></div>';
		echo '</div>';

		global $wpdb;
		$commissions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_commissions WHERE affiliate_id = (SELECT id FROM {$wpdb->prefix}edu_affiliates WHERE user_id = %d) ORDER BY created_at DESC LIMIT 5", get_current_user_id() ) );

		echo '<div class="edu-card" style="margin-top:20px;"><h3>' . esc_html__( 'Recent Referral Activity', 'edupreneur-pro' ) . '</h3>';
		if ( empty( $commissions ) ) {
			echo '<p>' . esc_html__( 'No referrals recorded yet. Start sharing your link to earn!', 'edupreneur-pro' ) . '</p>';
		} else {
			echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>';
			foreach ( $commissions as $comm ) {
				echo "<tr><td>{$comm->created_at}</td><td>\${$comm->amount}</td><td>{$comm->status}</td></tr>";
			}
			echo '</tbody></table>';
		}
		echo '</div></div>';
	}
	public function register_routes() {
		$controller = new AffiliateController();
		$controller->register_routes();
	}
	public function get_id() { return 'affiliate'; }
}
