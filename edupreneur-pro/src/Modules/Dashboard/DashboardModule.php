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
			__( 'Affiliate Payouts', 'edupreneur-pro' ),
			__( 'Payouts', 'edupreneur-pro' ),
			'manage_options',
			'edu-payouts',
			array( $this, 'render_payouts_page' )
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

		add_submenu_page(
			'edupreneur-pro',
			__( 'Course Categories', 'edupreneur-pro' ),
			__( 'Categories', 'edupreneur-pro' ),
			'manage_options',
			'edu-category-mgmt',
			array( $this, 'render_category_mgmt_page' )
		);

		add_submenu_page(
			'edupreneur-pro',
			__( 'Digital Products', 'edupreneur-pro' ),
			__( 'Digital Products', 'edupreneur-pro' ),
			'manage_options',
			'edu-product-mgmt',
			array( $this, 'render_product_mgmt_page' )
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
		if ( isset( $_GET['comm_action'] ) && $_GET['comm_action'] === 'mark_paid' && isset( $_GET['id'] ) ) {
			check_admin_referer( 'edu_comm_pay_action' );
			$wpdb->update( "{$wpdb->prefix}edu_commissions", array( 'status' => 'paid' ), array( 'id' => intval( $_GET['id'] ) ) );
			echo '<div class="updated"><p>Commission marked as paid.</p></div>';
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
		echo '</tbody></table>';

		echo '<h2 style="margin-top:30px;">Pending Commissions</h2>';
		$commissions = $wpdb->get_results( "SELECT c.*, u.display_name FROM {$wpdb->prefix}edu_commissions c JOIN {$wpdb->prefix}edu_affiliates a ON c.affiliate_id = a.id JOIN {$wpdb->users} u ON a.user_id = u.ID WHERE c.status = 'unpaid'" );
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Affiliate</th><th>Amount</th><th>Order ID</th><th>Actions</th></tr></thead><tbody>';
		foreach ( $commissions as $comm ) {
			$pay_url = wp_nonce_url( admin_url( 'admin.php?page=edu-affiliates&comm_action=mark_paid&id=' . $comm->id ), 'edu_comm_pay_action' );
			echo "<tr><td>{$comm->display_name}</td><td>\${$comm->amount}</td><td>#{$comm->order_id}</td>";
			echo "<td><a href='{$pay_url}' class='edu-btn' style='background:#28a745;'>Mark as Paid</a></td></tr>";
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
			} elseif ( $_GET['action'] === 'lock' ) {
				$wpdb->update( "{$wpdb->prefix}edu_community_posts", array( 'is_locked' => 1 ), array( 'id' => intval( $_GET['id'] ) ) );
			} elseif ( $_GET['action'] === 'unlock' ) {
				$wpdb->update( "{$wpdb->prefix}edu_community_posts", array( 'is_locked' => 0 ), array( 'id' => intval( $_GET['id'] ) ) );
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
			$lock_action = $post->is_locked ? 'unlock' : 'lock';
			$lock_label = $post->is_locked ? 'Unlock' : 'Lock';
			$lock_url = wp_nonce_url( admin_url( 'admin.php?page=edu-community-mgmt&action='.$lock_action.'&id=' . $post->id ), 'edu_comm_action' );

			echo "<tr><td>" . ( $user ? $user->display_name : 'Unknown' ) . "</td><td>" . esc_html( wp_trim_words( $post->content, 10 ) ) . "</td><td>" . ( $post->is_pinned ? 'Yes' : 'No' ) . "</td>";
			echo "<td><a href='{$pin_url}' class='edu-btn'>Pin</a> <a href='{$lock_url}' class='edu-btn' style='background:#6c757d;'>{$lock_label}</a> <a href='{$delete_url}' class='edu-btn' style='background:#dc3545;' onclick='return confirm(\"Delete post?\")'>Delete</a></td></tr>";
		}
		echo '</tbody></table></div>';
	}

	public function render_my_courses_page() {
		$plugin = \EdupreneurPro::instance();

		if ( isset( $_GET['edu_lesson'] ) || isset( $_GET['edu_course_id'] ) || isset( $_GET['buy_course'] ) || isset( $_GET['edu_category'] ) ) {
			echo '<div class="edu-admin-wrap">';
			echo $plugin->handle_lesson_display( '' );
			echo '</div>';
			return;
		}

		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'My Courses', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Pick up where you left off and master new skills.', 'edupreneur-pro' ) . '</p></header>';

		echo $plugin->render_student_dashboard();
		echo '</div>';
	}

	public function render_student_community_page() {
		echo '<div class="edu-admin-wrap">';
		echo '<header class="edu-header"><h1>' . esc_html__( 'Community Discussion Board', 'edupreneur-pro' ) . '</h1>';
		echo '<p>' . esc_html__( 'Connect with fellow learners and your instructors.', 'edupreneur-pro' ) . '</p></header>';

		$board = new \EdupreneurPro\Modules\Community\Services\DiscussionBoard();
		$posts = $board->get_results_with_locking( 0 ); // Updated service call

		echo '<div class="edu-card">';
		echo '<h3>' . esc_html__( 'Recent Activity', 'edupreneur-pro' ) . '</h3>';
		if ( empty( $posts ) ) {
			echo '<p>' . esc_html__( 'No activity yet. Be the first to start a conversation!', 'edupreneur-pro' ) . '</p>';
		} else {
			foreach ( $posts as $post ) {
				$user = get_userdata( $post->user_id );
				$locked_tag = $post->is_locked ? ' <span class="tag">Locked</span>' : '';
				echo '<div style="border-bottom:1px solid #eee; padding:10px 0;">';
				echo '<strong>' . ( $user ? esc_html( $user->display_name ) : 'Unknown' ) . '</strong>: ';
				echo esc_html( $post->content ) . $locked_tag;
				echo '</div>';
			}
		}

		$is_any_locked = false; // For global board, maybe check if a specific "thread" is locked?
		// Since we don't have threads yet, let's just allow posting unless a global lock is set?
		// Actually, let's just implement the UI for threads later.

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
			} elseif ( $_POST['edu_action'] === 'send_reminders' ) {
				$count = \EdupreneurPro\Core\SystemService::send_abandoned_reminders();
				echo '<div class="updated"><p>' . sprintf( __( '%d abandoned checkout reminders sent.', 'edupreneur-pro' ), $count ) . '</p></div>';
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

		echo '<form method="post" style="margin-top:20px; display:flex; flex-wrap:wrap; gap:10px;">';
		wp_nonce_field( 'edu_system_action' );
		echo '<button type="submit" name="edu_action" value="sample_data" class="edu-btn">' . esc_html__( 'Load Sample Data', 'edupreneur-pro' ) . '</button>';
		echo '<button type="submit" name="edu_action" value="send_reminders" class="edu-btn" style="background:#6c757d;">' . esc_html__( 'Process Abandoned Checkouts', 'edupreneur-pro' ) . '</button>';
		echo '<button type="submit" name="edu_action" value="clear_db" class="edu-btn" style="background:#dc3545;" onclick="return confirm(\'Are you sure? This will delete all courses and students.\')">' . esc_html__( 'Reset Database', 'edupreneur-pro' ) . '</button>';
		echo '</form></div>';

		echo '<div class="edu-card"><h3>' . esc_html__( 'Payment Gateway Keys', 'edupreneur-pro' ) . '</h3>';
		echo '<form method="post" style="margin-top:20px;">';
		wp_nonce_field( 'edu_system_action' );
		echo '<div class="edu-form-group"><label>Stripe Secret Key</label><input type="password" name="stripe_key" value="' . esc_attr( get_option( 'edu_stripe_key' ) ) . '">';
		echo '<p class="edu-field-caption">' . esc_html__( 'Your Stripe Secret Key from the Stripe Dashboard. Required for processing credit card payments.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-form-group"><label>PayPal Business Email</label><input type="email" name="paypal_email" value="' . esc_attr( get_option( 'edu_paypal_email' ) ) . '">';
		echo '<p class="edu-field-caption">' . esc_html__( 'The email address associated with your PayPal Business account for receiving payments.', 'edupreneur-pro' ) . '</p></div>';
		echo '<button type="submit" name="edu_action" value="save_keys" class="edu-btn">' . esc_html__( 'Save API Keys', 'edupreneur-pro' ) . '</button>';
		echo '</form></div>';
		echo '</div></div>';
	}

	public function render_category_mgmt_page() {
		global $wpdb;
		if ( isset( $_POST['edu_cat_action'] ) ) {
			check_admin_referer( 'edu_cat_action' );
			if ( $_POST['edu_cat_action'] === 'save' ) {
				$data = array( 'name' => sanitize_text_field( $_POST['name'] ), 'slug' => sanitize_title( $_POST['name'] ), 'description' => sanitize_textarea_field( $_POST['description'] ) );
				if ( ! empty( $_POST['cat_id'] ) ) {
					$wpdb->update( "{$wpdb->prefix}edu_categories", $data, array( 'id' => intval( $_POST['cat_id'] ) ) );
				} else {
					$wpdb->insert( "{$wpdb->prefix}edu_categories", $data );
				}
			} elseif ( $_POST['edu_cat_action'] === 'delete' ) {
				$wpdb->delete( "{$wpdb->prefix}edu_categories", array( 'id' => intval( $_POST['cat_id'] ) ) );
			}
			echo '<div class="updated"><p>Category updated.</p></div>';
		}
		$items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_categories ORDER BY name ASC" );
		echo '<div class="edu-admin-wrap"><h1>Manage Course Categories</h1>';
		echo '<form method="post" class="edu-card" style="margin-bottom:20px;">';
		wp_nonce_field( 'edu_cat_action' );
		echo '<h3>Add/Edit Category</h3>';
		echo '<input type="hidden" name="cat_id" id="cat_id">';
		echo '<div class="edu-form-group"><label>Name</label><input type="text" name="name" id="cat_name" required>';
		echo '<p class="edu-field-caption">' . esc_html__( 'The display name for this category (e.g., Business, Web Development).', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-form-group"><label>Description</label><textarea name="description" id="cat_desc" rows="3"></textarea>';
		echo '<p class="edu-field-caption">' . esc_html__( 'A brief summary of what courses in this category cover. Shown on category list pages.', 'edupreneur-pro' ) . '</p></div>';
		echo '<button type="submit" name="edu_cat_action" value="save" class="edu-btn">Save Category</button></form>';

		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Name</th><th>Slug</th><th>Actions</th></tr></thead><tbody>';
		if ( empty( $items ) ) {
			echo '<tr><td colspan="3">No categories found.</td></tr>';
		} else {
			foreach ( $items as $item ) {
				echo "<tr><td>" . esc_html( $item->name ) . "</td><td>" . esc_html( $item->slug ) . "</td><td>";
				$json_data = esc_attr( json_encode( $item ) );
				echo "<button type='button' class='edu-btn' onclick='eduEditCategory({$json_data})'>Edit</button> ";
				echo "<form method='post' style='display:inline;'>";
				wp_nonce_field( 'edu_cat_action' );
				echo "<input type='hidden' name='cat_id' value='{$item->id}'>";
				echo "<button type='submit' name='edu_cat_action' value='delete' class='edu-btn' style='background:#dc3545;' onclick='return confirm(\"Delete category?\")'>Delete</button></form></td></tr>";
			}
		}
		echo '</tbody></table></div>';
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
		echo '<div class="edu-form-group"><label>Title</label><input type="text" name="title" id="kb_title" required>';
		echo '<p class="edu-field-caption">' . esc_html__( 'The headline of the support article or guide.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-form-group"><label>Category</label><input type="text" name="category" id="kb_category" placeholder="e.g. general, payments">';
		echo '<p class="edu-field-caption">' . esc_html__( 'Internal grouping for articles (e.g., technical, onboarding).', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-form-group"><label>Content</label><textarea name="content" id="kb_content" rows="5" required></textarea>';
		echo '<p class="edu-field-caption">' . esc_html__( 'The full text of your guide. You can use basic HTML here.', 'edupreneur-pro' ) . '</p></div>';
		echo '<button type="submit" name="edu_kb_action" value="save" class="edu-btn">Save Article</button></form>';

		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Title</th><th>Category</th><th>Actions</th></tr></thead><tbody>';
		foreach ( $items as $item ) {
			echo "<tr><td>" . esc_html( $item->title ) . "</td><td>" . esc_html( $item->category ) . "</td><td>";
			$json_data = esc_attr( json_encode( $item ) );
			echo "<button type='button' class='edu-btn' onclick='eduEditKB({$json_data})'>Edit</button> ";
			echo "<form method='post' style='display:inline;'>";
			wp_nonce_field( 'edu_kb_action' );
			echo "<input type='hidden' name='kb_id' value='{$item->id}'>";
			echo "<button type='submit' name='edu_kb_action' value='delete' class='edu-btn' style='background:#dc3545;' onclick='return confirm(\"Delete article?\")'>Delete</button></form></td></tr>";
		}
		echo '</tbody></table></div>';
	}

	public function render_payouts_page() {
		global $wpdb;
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'approve' && isset( $_GET['id'] ) ) {
			check_admin_referer( 'edu_payout_admin' );
			$payout_id = intval( $_GET['id'] );
			$payout = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_payouts WHERE id = %d", $payout_id ) );

			if ( $payout ) {
				$wpdb->update( "{$wpdb->prefix}edu_payouts", array( 'status' => 'paid' ), array( 'id' => $payout_id ) );
				// Mark associated commissions as paid
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}edu_commissions SET status = 'paid' WHERE affiliate_id = %d AND status = 'processing'", $payout->affiliate_id ) );

				\EdupreneurPro\Core\SystemService::log_action( get_current_user_id(), 'PAYOUT_APPROVE', 'payout', $payout_id, 'Approved payout for affiliate #' . $payout->affiliate_id );
				echo '<div class="updated"><p>' . __( 'Payout approved and marked as paid.', 'edupreneur-pro' ) . '</p></div>';
			}
		}

		$payouts = $wpdb->get_results( "SELECT p.*, u.display_name FROM {$wpdb->prefix}edu_payouts p JOIN {$wpdb->prefix}edu_affiliates a ON p.affiliate_id = a.id JOIN {$wpdb->users} u ON a.user_id = u.ID ORDER BY p.created_at DESC" );

		echo '<div class="edu-admin-wrap"><h1>Affiliate Payout Requests</h1>';
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Date</th><th>Affiliate</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
		if ( empty( $payouts ) ) {
			echo '<tr><td colspan="5">No payout requests found.</td></tr>';
		} else {
			foreach ( $payouts as $p ) {
				echo "<tr><td>{$p->created_at}</td><td>" . esc_html( $p->display_name ) . "</td><td>\${$p->amount}</td><td>{$p->status}</td><td>";
				if ( $p->status === 'pending' ) {
					$url = wp_nonce_url( admin_url( 'admin.php?page=edu-payouts&action=approve&id=' . $p->id ), 'edu_payout_admin' );
					echo "<a href='{$url}' class='edu-btn' style='background:#28a745;'>Approve & Mark Paid</a>";
				}
				echo "</td></tr>";
			}
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

	public function render_product_mgmt_page() {
		global $wpdb;
		if ( isset( $_POST['edu_prod_action'] ) ) {
			check_admin_referer( 'edu_prod_action' );
			if ( $_POST['edu_prod_action'] === 'save' ) {
				$data = array(
					'title'          => sanitize_text_field( $_POST['title'] ),
					'price'          => floatval( $_POST['price'] ),
					'file_url'       => esc_url_raw( $_POST['file_url'] ),
					'download_limit' => intval( $_POST['download_limit'] ),
					'expiry_days'    => intval( $_POST['expiry_days'] )
				);
				if ( ! empty( $_POST['prod_id'] ) ) {
					$wpdb->update( "{$wpdb->prefix}edu_products", $data, array( 'id' => intval( $_POST['prod_id'] ) ) );
				} else {
					$wpdb->insert( "{$wpdb->prefix}edu_products", $data );
				}
			} elseif ( $_POST['edu_prod_action'] === 'delete' ) {
				$wpdb->delete( "{$wpdb->prefix}edu_products", array( 'id' => intval( $_POST['prod_id'] ) ) );
			}
			echo '<div class="updated"><p>Product updated.</p></div>';
		}
		$items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_products ORDER BY title ASC" );
		echo '<div class="edu-admin-wrap"><h1>Manage Digital Products</h1>';
		echo '<form method="post" class="edu-card" style="margin-bottom:20px;">';
		wp_nonce_field( 'edu_prod_action' );
		echo '<h3>Add/Edit Product</h3>';
		echo '<input type="hidden" name="prod_id" id="prod_id">';
		echo '<div class="edu-form-group"><label>Title</label><input type="text" name="title" id="prod_title" required>';
		echo '<p class="edu-field-caption">' . esc_html__( 'The name of the digital product as it appears in the store.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-form-group"><label>Price ($)</label><input type="number" step="0.01" name="price" id="prod_price" value="0.00">';
		echo '<p class="edu-field-caption">' . esc_html__( 'The cost for a single download license.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-form-group"><label>File URL</label><input type="text" name="file_url" id="prod_url">';
		echo '<p class="edu-field-caption">' . esc_html__( 'The path to the file. This can be a local path or a remote URL.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-form-group"><label>Download Limit (0 for unlimited)</label><input type="number" name="download_limit" id="prod_limit" value="0">';
		echo '<p class="edu-field-caption">' . esc_html__( 'How many times the user can download the file after purchase.', 'edupreneur-pro' ) . '</p></div>';
		echo '<div class="edu-form-group"><label>Expiry Days (0 for no expiry)</label><input type="number" name="expiry_days" id="prod_expiry" value="0">';
		echo '<p class="edu-field-caption">' . esc_html__( 'Number of days the download link remains valid after purchase.', 'edupreneur-pro' ) . '</p></div>';
		echo '<button type="submit" name="edu_prod_action" value="save" class="edu-btn">Save Product</button></form>';

		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Title</th><th>Price</th><th>Limit</th><th>Actions</th></tr></thead><tbody>';
		if ( empty( $items ) ) {
			echo '<tr><td colspan="4">No products found.</td></tr>';
		} else {
			foreach ( $items as $item ) {
				echo "<tr><td>" . esc_html( $item->title ) . "</td><td>\${$item->price}</td><td>" . ( $item->download_limit ?: 'Unlimited' ) . "</td><td>";
				$json_data = esc_attr( json_encode( $item ) );
				echo "<button type='button' class='edu-btn' onclick='eduEditProduct({$json_data})'>Edit</button> ";
				echo "<form method='post' style='display:inline;'>";
				wp_nonce_field( 'edu_prod_action' );
				echo "<input type='hidden' name='prod_id' value='{$item->id}'>";
				echo "<button type='submit' name='edu_prod_action' value='delete' class='edu-btn' style='background:#dc3545;' onclick='return confirm(\"Delete product?\")'>Delete</button></form></td></tr>";
			}
		}
		echo '</tbody></table></div>';
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

		echo '<div class="edu-card" style="margin-top:20px;">';
		echo '<h3>' . esc_html__( 'Tax-Ready Financial Report', 'edupreneur-pro' ) . '</h3>';
		echo '<p>' . esc_html__( 'Export-ready summary of your business earnings and estimated tax obligations.', 'edupreneur-pro' ) . '</p>';
		echo '<table class="wp-list-table widefat fixed striped" style="margin-top:15px;">';
		echo '<thead><tr><th>Description</th><th>Amount</th></tr></thead><tbody>';
		echo '<tr><td>Total Gross Revenue</td><td>$' . number_format($stats['gross'], 2) . '</td></tr>';
		echo '<tr><td>Total Refunds</td><td>-$' . number_format($stats['refunds'], 2) . '</td></tr>';
		echo '<tr><td><strong>Net Taxable Income</strong></td><td><strong>$' . number_format($stats['net'], 2) . '</strong></td></tr>';
		$est_tax = $stats['net'] * 0.20; // 20% estimated tax
		echo '<tr><td>Estimated Tax Liability (20%)</td><td>$' . number_format($est_tax, 2) . '</td></tr>';
		echo '</tbody></table>';
		echo '<button class="edu-btn" style="margin-top:15px;" onclick="window.print()">Print for Accounting</button>';
		echo '</div>';

		echo '</div>';
	}

	public function get_id() {
		return 'dashboard';
	}
}
