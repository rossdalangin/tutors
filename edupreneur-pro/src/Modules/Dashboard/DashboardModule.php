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

		add_submenu_page(
			'edupreneur-pro',
			__( 'Orders Management', 'edupreneur-pro' ),
			__( 'Orders', 'edupreneur-pro' ),
			'manage_options',
			'edu-orders',
			array( $this, 'render_orders_page' )
		);

		add_submenu_page(
			'edupreneur-pro',
			__( 'Affiliate Management', 'edupreneur-pro' ),
			__( 'Affiliates', 'edupreneur-pro' ),
			'manage_options',
			'edu-affiliates',
			array( $this, 'render_affiliates_page' )
		);

		add_submenu_page(
			'edupreneur-pro',
			__( 'Student Management', 'edupreneur-pro' ),
			__( 'Students', 'edupreneur-pro' ),
			'manage_options',
			'edu-students',
			array( $this, 'render_students_page' )
		);

		add_submenu_page(
			'edupreneur-pro',
			__( 'Community Moderation', 'edupreneur-pro' ),
			__( 'Community', 'edupreneur-pro' ),
			'manage_options',
			'edu-community-mgmt',
			array( $this, 'render_community_mgmt_page' )
		);
	}

	public function render_orders_page() {
		global $wpdb;
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['id'] ) ) {
			check_admin_referer( 'edu_order_action' );
			$wpdb->delete( "{$wpdb->prefix}edu_orders", array( 'id' => intval( $_GET['id'] ) ) );
			echo '<div class="updated"><p>Order deleted.</p></div>';
		}
		$orders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_orders ORDER BY created_at DESC" );
		echo '<div class="edu-admin-wrap"><h1>Order Management</h1><table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr><th>ID</th><th>User</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
		foreach ( $orders as $order ) {
			$user = get_userdata( $order->user_id );
			$delete_url = wp_nonce_url( admin_url( 'admin.php?page=edu-orders&action=delete&id=' . $order->id ), 'edu_order_action' );
			echo "<tr><td>{$order->id}</td><td>" . ( $user ? $user->display_name : 'Unknown' ) . "</td><td>\${$order->total_amount}</td><td>{$order->status}</td>";
			echo "<td><a href='{$delete_url}' class='edu-btn' style='background:#dc3545;' onclick='return confirm(\"Delete order?\")'>Delete</a></td></tr>";
		}
		echo '</tbody></table></div>';
	}

	public function render_affiliates_page() {
		global $wpdb;
		if ( isset( $_POST['edu_affiliate_id'] ) ) {
			check_admin_referer( 'edu_affiliate_action' );
			if ( $_POST['edu_action'] === 'update' ) {
				$wpdb->update( "{$wpdb->prefix}edu_affiliates", array( 'status' => sanitize_text_field( $_POST['status'] ), 'commission_rate' => floatval( $_POST['rate'] ) ), array( 'id' => intval( $_POST['edu_affiliate_id'] ) ) );
			} elseif ( $_POST['edu_action'] === 'delete' ) {
				$wpdb->delete( "{$wpdb->prefix}edu_affiliates", array( 'id' => intval( $_POST['edu_affiliate_id'] ) ) );
			}
			echo '<div class="updated"><p>Affiliate updated.</p></div>';
		}
		$affiliates = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_affiliates" );
		echo '<div class="edu-admin-wrap"><h1>Affiliate Management</h1><table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr><th>User</th><th>Code</th><th>Rate (%)</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
		foreach ( $affiliates as $aff ) {
			$user = get_userdata( $aff->user_id );
			echo "<tr><form method='post'><td>" . ( $user ? $user->display_name : 'Unknown' ) . "</td><td>{$aff->referral_code}</td>";
			echo "<td><input type='number' name='rate' value='{$aff->commission_rate}' style='width:60px;'></td>";
			echo "<td><select name='status'><option " . selected( $aff->status, 'active', false ) . ">active</option><option " . selected( $aff->status, 'pending', false ) . ">pending</option></select></td>";
			echo "<td>" . wp_nonce_field( 'edu_affiliate_action', '_wpnonce', true, false );
			echo "<input type='hidden' name='edu_affiliate_id' value='{$aff->id}'>";
			echo "<button type='submit' name='edu_action' value='update' class='edu-btn'>Save</button> ";
			echo "<button type='submit' name='edu_action' value='delete' class='edu-btn' style='background:#dc3545;' onclick='return confirm(\"Delete affiliate?\")'>Delete</button></td></form></tr>";
		}
		echo '</tbody></table></div>';
	}

	public function render_community_mgmt_page() {
		global $wpdb;
		if ( isset( $_GET['action'] ) && isset( $_GET['id'] ) ) {
			check_admin_referer( 'edu_comm_action' );
			if ( $_GET['action'] === 'delete' ) {
				$wpdb->delete( "{$wpdb->prefix}edu_community_posts", array( 'id' => intval( $_GET['id'] ) ) );
			} elseif ( $_GET['action'] === 'pin' ) {
				$wpdb->update( "{$wpdb->prefix}edu_community_posts", array( 'is_pinned' => 1 ), array( 'id' => intval( $_GET['id'] ) ) );
			}
			echo '<div class="updated"><p>Action completed.</p></div>';
		}
		$posts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_community_posts ORDER BY created_at DESC" );
		echo '<div class="edu-admin-wrap"><h1>Community Moderation</h1><table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr><th>User</th><th>Content</th><th>Pinned</th><th>Actions</th></tr></thead><tbody>';
		foreach ( $posts as $post ) {
			$user = get_userdata( $post->user_id );
			$delete_url = wp_nonce_url( admin_url( 'admin.php?page=edu-community-mgmt&action=delete&id=' . $post->id ), 'edu_comm_action' );
			$pin_url = wp_nonce_url( admin_url( 'admin.php?page=edu-community-mgmt&action=pin&id=' . $post->id ), 'edu_comm_action' );
			echo "<tr><td>" . ( $user ? $user->display_name : 'Unknown' ) . "</td><td>" . esc_html( wp_trim_words( $post->content, 10 ) ) . "</td><td>" . ( $post->is_pinned ? 'Yes' : 'No' ) . "</td>";
			echo "<td><a href='{$pin_url}' class='edu-btn'>Pin</a> <a href='{$delete_url}' class='edu-btn' style='background:#dc3545;' onclick='return confirm(\"Delete post?\")'>Delete</a></td></tr>";
		}
		echo '</tbody></table></div>';
	}

	public function render_students_page() {
		global $wpdb;
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete_enroll' && isset( $_GET['id'] ) ) {
			check_admin_referer( 'edu_student_action' );
			$wpdb->delete( "{$wpdb->prefix}edu_enrollments", array( 'id' => intval( $_GET['id'] ) ) );
			echo '<div class="updated"><p>Enrollment removed.</p></div>';
		}
		$enrollments = $wpdb->get_results( "SELECT e.*, c.title as course_title FROM {$wpdb->prefix}edu_enrollments e JOIN {$wpdb->prefix}edu_courses c ON e.course_id = c.id" );
		echo '<div class="edu-admin-wrap"><h1>Student Enrollments</h1><table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr><th>Student</th><th>Course</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
		foreach ( $enrollments as $en ) {
			$user = get_userdata( $en->student_id );
			$delete_url = wp_nonce_url( admin_url( 'admin.php?page=edu-students&action=delete_enroll&id=' . $en->id ), 'edu_student_action' );
			echo "<tr><td>" . ( $user ? $user->display_name : 'Unknown' ) . "</td><td>{$en->course_title}</td><td>{$en->enrolled_at}</td><td>{$en->status}</td>";
			echo "<td><a href='{$delete_url}' class='edu-btn' style='background:#dc3545;' onclick='return confirm(\"Remove student from course?\")'>Unenroll</a></td></tr>";
		}
		echo '</tbody></table></div>';
	}

	public function render_settings_page() {
		if ( isset( $_POST['edu_action'] ) && check_admin_referer( 'edu_system_action' ) ) {
			if ( $_POST['edu_action'] === 'clear_db' ) {
				\EdupreneurPro\Core\SystemService::clear_database();
				echo '<div class="updated"><p>Database cleared successfully.</p></div>';
			} elseif ( $_POST['edu_action'] === 'sample_data' ) {
				\EdupreneurPro\Core\SystemService::add_sample_data();
				echo '<div class="updated"><p>Sample data injected successfully.</p></div>';
			} elseif ( $_POST['edu_action'] === 'save_keys' ) {
				update_option( 'edu_stripe_key', sanitize_text_field( $_POST['stripe_key'] ) );
				update_option( 'edu_paypal_email', sanitize_email( $_POST['paypal_email'] ) );
				echo '<div class="updated"><p>Keys saved.</p></div>';
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

		echo '<div class="edu-card"><h3>' . esc_html__( 'Payment Gateway Keys', 'edupreneur-pro' ) . '</h3>';
		echo '<form method="post" style="margin-top:20px;">';
		wp_nonce_field( 'edu_system_action' );
		echo '<div class="edu-form-group"><label>Stripe Secret Key</label><input type="password" name="stripe_key" value="' . esc_attr( get_option( 'edu_stripe_key' ) ) . '"></div>';
		echo '<div class="edu-form-group"><label>PayPal Business Email</label><input type="email" name="paypal_email" value="' . esc_attr( get_option( 'edu_paypal_email' ) ) . '"></div>';
		echo '<button type="submit" name="edu_action" value="save_keys" class="edu-btn">' . esc_html__( 'Save API Keys', 'edupreneur-pro' ) . '</button>';
		echo '</form></div>';
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
