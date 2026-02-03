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

		add_submenu_page(
			'edupreneur-pro',
			__( 'My Enrolled Courses', 'edupreneur-pro' ),
			__( 'My Courses', 'edupreneur-pro' ),
			'view_edu_courses',
			'edu-my-courses',
			array( $this, 'render_my_courses_page' )
		);

		add_submenu_page(
			'edupreneur-pro',
			__( 'Student Community', 'edupreneur-pro' ),
			__( 'Community Board', 'edupreneur-pro' ),
			'view_edu_community',
			'edu-student-community',
			array( $this, 'render_student_community_page' )
		);

		add_submenu_page(
			'edupreneur-pro',
			__( 'My Purchase History', 'edupreneur-pro' ),
			__( 'My Orders', 'edupreneur-pro' ),
			'view_edu_orders',
			'edu-my-orders',
			array( $this, 'render_my_orders_page' )
		);

		add_submenu_page(
			'edupreneur-pro',
			__( 'Manage Knowledge Base', 'edupreneur-pro' ),
			__( 'Manage KB', 'edupreneur-pro' ),
			'manage_options',
			'edu-kb-mgmt',
			array( $this, 'render_kb_mgmt_page' )
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

	public function render_my_courses_page() {
		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'My Courses', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Pick up where you left off and master new skills.', 'edupreneur-pro' ) . '</p></header>';

		// Use the same logic as the shortcode but rendered in admin
		$plugin = \EdupreneurPro::instance();
		echo $plugin->render_student_dashboard();
		echo '</div>';
	}

	public function render_student_community_page() {
		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'Community Discussion Board', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Connect with fellow learners and your instructors.', 'edupreneur-pro' ) . '</p></header>';

		$board = new \EdupreneurPro\Modules\Community\Services\DiscussionBoard();
		$posts = $board->get_posts( 0 ); // Global posts for demo

		echo '<div class="edu-card">';
		echo '<h3>' . esc_html__( 'Recent Activity', 'edupreneur-pro' ) . '</h3>';
		if ( empty( $posts ) ) {
			echo '<p>' . esc_html__( 'No activity yet. Be the first to start a conversation!', 'edupreneur-pro' ) . '</p>';
		} else {
			foreach ( $posts as $post ) {
				$user = get_userdata( $post->user_id );
				echo '<div style="border-bottom:1px solid #eee; padding:10px 0;">';
				echo '<strong>' . ( $user ? esc_html( $user->display_name ) : 'Unknown' ) . '</strong>: ';
				echo esc_html( $post->content );
				echo '</div>';
			}
		}
		echo '<form method="post" style="margin-top:20px;">';
		wp_nonce_field( 'edu_new_post' );
		echo '<textarea name="content" style="width:100%;" placeholder="What is on your mind?"></textarea>';
		echo '<button type="submit" class="edu-btn" style="margin-top:10px;">Post to Community</button>';
		echo '</form></div></div>';

		if ( isset( $_POST['content'] ) && check_admin_referer( 'edu_new_post' ) ) {
			$board->create_post( array( 'content' => $_POST['content'], 'course_id' => 0 ) );
			echo '<script>location.reload();</script>';
		}
	}

	public function render_my_orders_page() {
		global $wpdb;
		$user_id = get_current_user_id();
		$orders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_orders WHERE user_id = %d ORDER BY created_at DESC", $user_id ) );

		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'My Purchase History', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Manage your invoices and course access.', 'edupreneur-pro' ) . '</p></header>';

		if ( empty( $orders ) ) {
			echo '<div class="edu-card"><p>' . esc_html__( 'You haven\'t made any purchases yet.', 'edupreneur-pro' ) . '</p></div>';
		} else {
			echo '<table class="wp-list-table widefat fixed striped">';
			echo '<thead><tr><th>Order ID</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>';
			foreach ( $orders as $order ) {
				echo "<tr><td>#{$order->id}</td><td>\${$order->total_amount}</td><td>{$order->status}</td><td>{$order->created_at}</td></tr>";
			}
			echo '</tbody></table>';
		}
		echo '</div>';
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

	public function render_kb_mgmt_page() {
		global $wpdb;
		if ( isset( $_POST['edu_kb_action'] ) ) {
			check_admin_referer( 'edu_kb_action' );
			if ( $_POST['edu_kb_action'] === 'save' ) {
				$data = array( 'title' => sanitize_text_field( $_POST['title'] ), 'content' => wp_kses_post( $_POST['content'] ), 'category' => sanitize_text_field( $_POST['category'] ) );
				if ( ! empty( $_POST['kb_id'] ) ) {
					$wpdb->update( "{$wpdb->prefix}edu_kb", $data, array( 'id' => intval( $_POST['kb_id'] ) ) );
				} else {
					$wpdb->insert( "{$wpdb->prefix}edu_kb", $data );
				}
			} elseif ( $_POST['edu_kb_action'] === 'delete' ) {
				$wpdb->delete( "{$wpdb->prefix}edu_kb", array( 'id' => intval( $_POST['kb_id'] ) ) );
			}
			echo '<div class="updated"><p>Knowledge Base updated.</p></div>';
		}
		$items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_kb" );
		echo '<div class="edu-admin-wrap"><h1>Manage Knowledge Base</h1>';
		echo '<form method="post" class="edu-card" style="margin-bottom:20px;">';
		wp_nonce_field( 'edu_kb_action' );
		echo '<h3>Add/Edit Article</h3>';
		echo '<input type="hidden" name="kb_id" id="kb_id">';
		echo '<div class="edu-form-group"><label>Title</label><input type="text" name="title" id="kb_title" required></div>';
		echo '<div class="edu-form-group"><label>Category</label><input type="text" name="category" id="kb_category" placeholder="e.g. general, payments"></div>';
		echo '<div class="edu-form-group"><label>Content</label><textarea name="content" id="kb_content" rows="5" required></textarea></div>';
		echo '<button type="submit" name="edu_kb_action" value="save" class="edu-btn">Save Article</button></form>';

		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Title</th><th>Category</th><th>Actions</th></tr></thead><tbody>';
		foreach ( $items as $item ) {
			echo "<tr><td>" . esc_html( $item->title ) . "</td><td>" . esc_html( $item->category ) . "</td><td>";
			echo "<button class='edu-btn' onclick='document.getElementById(\"kb_id\").value=\"{$item->id}\";document.getElementById(\"kb_title\").value=\"".esc_js($item->title)."\";document.getElementById(\"kb_category\").value=\"".esc_js($item->category)."\";document.getElementById(\"kb_content\").value=\"".esc_js($item->content)."\";'>Edit</button> ";
			echo "<form method='post' style='display:inline;'>";
			wp_nonce_field( 'edu_kb_action' );
			echo "<input type='hidden' name='kb_id' value='{$item->id}'>";
			echo "<button type='submit' name='edu_kb_action' value='delete' class='edu-btn' style='background:#dc3545;' onclick='return confirm(\"Delete article?\")'>Delete</button></form></td></tr>";
		}
		echo '</tbody></table></div>';
	}

	public function render_help_page() {
		global $wpdb;
		$items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_kb" );
		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'EdupreneurPro Knowledge Base', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Master every feature of your education business system. From setup to scale, we have you covered.', 'edupreneur-pro' ) . '</p></header>';

		if ( empty( $items ) ) {
			echo '<p>No guides available yet. Tutors can add them in the Management section.</p>';
		} else {
			echo '<div class="edu-grid">';
			foreach ( $items as $item ) {
				echo '<div class="edu-card"><h3>' . esc_html( $item->title ) . '</h3>';
				echo '<p>' . wp_kses_post( $item->content ) . '</p>';
				echo '<span class="tag">' . esc_html( $item->category ) . '</span></div>';
			}
			echo '</div>';
		}

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

		echo '<div class="edu-grid">';
		echo '<div class="edu-card"><h3>' . esc_html__( 'Gross Sales', 'edupreneur-pro' ) . '</h3><div class="edu-stat-val">$' . number_format( $stats['gross'], 2 ) . '</div><p>' . esc_html__( 'Total revenue generated.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-card"><h3>' . esc_html__( 'Net Profit', 'edupreneur-pro' ) . '</h3><div class="edu-stat-val">$' . number_format( $stats['net'], 2 ) . '</div><p>' . esc_html__( 'Revenue minus refunds/fees.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-card"><h3>' . esc_html__( 'Total Students', 'edupreneur-pro' ) . '</h3><div class="edu-stat-val">' . number_format( $stats['student_count'] ) . '</div><p>' . esc_html__( 'Active learners on platform.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-card"><h3>' . esc_html__( 'Orders', 'edupreneur-pro' ) . '</h3><div class="edu-stat-val">' . number_format( $stats['order_count'] ) . '</div><p>' . esc_html__( 'Successful transactions.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-card"><h3>' . esc_html__( 'Avg. Completion', 'edupreneur-pro' ) . '</h3><div class="edu-stat-val">' . $stats['avg_completion'] . '%</div><p>' . esc_html__( 'Course progress rate.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-card"><h3>' . esc_html__( 'Avg. Order Value', 'edupreneur-pro' ) . '</h3><div class="edu-stat-val">$' . number_format( $stats['avg_order_value'], 2 ) . '</div><p>' . esc_html__( 'Revenue per order.', 'edupreneur-pro' ) . '</p></div>';
		echo '</div>';

		echo '<div class="edu-card" style="margin-top:20px;">';
		echo '<h3>' . esc_html__( 'Business Health Check', 'edupreneur-pro' ) . '</h3>';
		echo '<div style="background:#f8f9fa; padding:20px; border-radius:8px; border-left:5px solid var(--edu-primary);">';
		if ( $stats['avg_completion'] > 70 ) {
			echo '<p style="color:#28a745; font-weight:600;">✅ Excellent student engagement!</p>';
		} else {
			echo '<p style="color:#856404; font-weight:600;">⚠️ Consider adding more interactive resources to boost completion rates.</p>';
		}
		echo '<p>' . sprintf( __( 'You have generated %d orders with an average value of $%s.', 'edupreneur-pro' ), $stats['order_count'], number_format( $stats['avg_order_value'], 2 ) ) . '</p>';
		echo '</div></div>';

		echo '</div>';
	}

	public function get_id() {
		return 'dashboard';
	}
}
