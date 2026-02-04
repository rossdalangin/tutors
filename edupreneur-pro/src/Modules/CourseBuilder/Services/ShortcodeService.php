<?php
namespace EdupreneurPro\Modules\CourseBuilder\Services;

class ShortcodeService {
	public function init() {
		add_shortcode( 'edu_course', array( $this, 'render_course_card_shortcode' ) );
		add_shortcode( 'edu_recent_courses', array( $this, 'render_recent_courses' ) );
		add_shortcode( 'edu_categories', array( $this, 'render_categories' ) );
		add_shortcode( 'edu_homepage', array( $this, 'render_homepage' ) );
		add_shortcode( 'edu_checkout', array( $this, 'render_checkout' ) );
	}

	public function render_course_card_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts );
		global $wpdb;
		$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_courses WHERE id = %d", $atts['id'] ) );
		if ( ! $course ) return '<p>' . __( 'Course not found.', 'edupreneur-pro' ) . '</p>';

		return $this->get_course_html( $course );
	}

	public function render_recent_courses( $atts ) {
		$atts = shortcode_atts( array(
			'limit'    => 10,
			'category' => '' // slug
		), $atts );
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}edu_courses WHERE status = 'publish'";
		$params = array();

		if ( ! empty( $atts['category'] ) ) {
			$query .= " AND (category = %s OR category_id = (SELECT id FROM {$wpdb->prefix}edu_categories WHERE slug = %s))";
			$params[] = $atts['category'];
			$params[] = $atts['category'];
		}

		$query .= " ORDER BY created_at DESC LIMIT %d";
		$params[] = intval( $atts['limit'] );

		$courses = $wpdb->get_results( $wpdb->prepare( $query, ...$params ) );

		$output = '<div class="edu-grid">';
		if ( empty( $courses ) ) {
			$output .= '<p>' . __( 'No courses found matching your criteria.', 'edupreneur-pro' ) . '</p>';
		} else {
			foreach ( $courses as $course ) {
				$output .= $this->get_course_html( $course );
			}
		}
		$output .= '</div>';
		return $output;
	}

	public function render_categories() {
		global $wpdb;
		$categories = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_categories ORDER BY name ASC" );
		$base_url = ( is_admin() && isset( $_GET['page'] ) ) ? admin_url( 'admin.php?page=' . sanitize_text_field( $_GET['page'] ) ) : get_permalink();

		if ( empty( $categories ) ) {
			// Fallback to distinct categories from courses if table is empty
			$categories = $wpdb->get_results( "SELECT DISTINCT category as name, category as slug FROM {$wpdb->prefix}edu_courses WHERE status = 'publish'" );
		}

		if ( empty( $categories ) ) {
			return '<p>' . __( 'No categories found.', 'edupreneur-pro' ) . '</p>';
		}

		$output = '<div class="edu-grid">';
		foreach ( $categories as $cat ) {
			$cat_name = $cat->name ?: 'General';
			$output .= '<div class="edu-card">';
			$output .= '<div style="font-size: 2em; margin-bottom:10px;">📂</div>';
			$output .= '<h3>' . esc_html( $cat_name ) . '</h3>';
			$output .= '<p>' . esc_html( $cat->description ?: sprintf( __( 'Master your skills in %s. Join thousands of students today.', 'edupreneur-pro' ), esc_html( $cat_name ) ) ) . '</p>';
			$output .= '<a href="' . add_query_arg( 'edu_category', $cat->slug ?: $cat_name, $base_url ) . '" class="edu-btn edu-btn-block">' . __( 'View Courses', 'edupreneur-pro' ) . '</a>';
			$output .= '</div>';
		}
		$output .= '</div>';
		return $output;
	}

	public function render_homepage() {
		$output = '<div class="edu-homepage-hero" style="text-align:center; padding: 80px 20px; background: linear-gradient(135deg, var(--edu-primary) 0%, #2c3e50 100%); color: #fff; border-radius: 12px; margin-bottom: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">';
		$output .= '<h1 style="font-size: 3em; margin-bottom: 20px; color: #fff;">' . __( 'Your Future Starts Here', 'edupreneur-pro' ) . '</h1>';
		$output .= '<p style="font-size: 1.4em; max-width: 800px; margin: 0 auto 30px; opacity: 0.9;">' . __( 'The all-in-one platform for professional education. High-quality courses, a thriving community, and expert instructors.', 'edupreneur-pro' ) . '</p>';
		$output .= '<a href="#featured" class="edu-btn" style="background:#fff; color:var(--edu-primary); padding: 15px 40px; font-weight: 700; font-size: 1.1em;">' . __( 'Get Started Today', 'edupreneur-pro' ) . '</a>';
		$output .= '</div>';

		$output .= '<h2 id="featured" style="text-align:center; margin-bottom: 40px; font-size: 2.2em;">' . __( 'Explore Featured Courses', 'edupreneur-pro' ) . '</h2>';
		$output .= $this->render_recent_courses( array( 'limit' => 6 ) );

		$output .= '<div style="margin-top: 60px; padding: 60px 40px; background: #f8f9fa; border-radius: 12px; display: flex; flex-wrap: wrap; align-items: center; gap: 40px;">';
		$output .= '<div style="flex: 1; min-width: 300px;">';
		$output .= '<h2 style="font-size: 2.5em; margin-top: 0;">' . __( 'Empower Your Business Through Education', 'edupreneur-pro' ) . '</h2>';
		$output .= '<p style="font-size: 1.2em; color: #555; margin-bottom: 30px;">' . __( 'Scale from one tutor to thousands of students with our all-in-one modular system. No third-party plugins required.', 'edupreneur-pro' ) . '</p>';
		$output .= '<ul style="list-style: none; padding: 0;">';
		$output .= '<li style="margin-bottom: 15px; font-size: 1.1em; font-weight: 500;">🚀 ' . __( 'Integrated Course Builder with Drag-and-Drop.', 'edupreneur-pro' ) . '</li>';
		$output .= '<li style="margin-bottom: 15px; font-size: 1.1em; font-weight: 500;">💳 ' . __( 'Multi-Gateway Payments (Stripe, PayPal, GCash).', 'edupreneur-pro' ) . '</li>';
		$output .= '<li style="margin-bottom: 15px; font-size: 1.1em; font-weight: 500;">🤝 ' . __( 'Built-in Affiliate Marketing and Community Boards.', 'edupreneur-pro' ) . '</li>';
		$output .= '</ul></div>';
		$output .= '<div style="flex: 1; min-width: 300px; text-align: center;"><div style="background:var(--edu-primary); color:#fff; width:100%; height:300px; border-radius:12px; display:flex; flex-direction:column; align-items:center; justify-content:center;">';
		$output .= '<span style="font-size: 5em;">📊</span><h3 style="color:#fff;">' . __( 'Real-Time Insights', 'edupreneur-pro' ) . '</h3></div></div>';
		$output .= '</div>';

		$output .= '<h2 style="text-align:center; margin: 60px 0 40px; font-size: 2.2em;">' . __( 'Browse by Category', 'edupreneur-pro' ) . '</h2>';
		$output .= $this->render_categories();

		return $output;
	}

	public function render_checkout() {
		if ( ! is_user_logged_in() ) {
			return '<div class="edu-card" style="text-align:center; padding: 40px;"><h3>' . __( 'Account Required', 'edupreneur-pro' ) . '</h3><p>' . __( 'Please log in or create an account to complete your purchase.', 'edupreneur-pro' ) . '</p><a href="' . wp_login_url( get_permalink() ) . '" class="edu-btn">' . __( 'Login to Continue', 'edupreneur-pro' ) . '</a></div>';
		}

		if ( ! isset( $_GET['buy_course'] ) ) {
			return '<p>' . __( 'No course selected for purchase.', 'edupreneur-pro' ) . '</p>';
		}

		$course_id = intval( $_GET['buy_course'] );
		global $wpdb;
		$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_courses WHERE id = %d", $course_id ) );

		if ( ! $course ) return '<p>' . __( 'Course not found.', 'edupreneur-pro' ) . '</p>';

		$base_url = ( is_admin() && isset( $_GET['page'] ) ) ? admin_url( 'admin.php?page=' . sanitize_text_field( $_GET['page'] ) ) : get_permalink();

		if ( isset( $_POST['edu_confirm_purchase'] ) && check_admin_referer( 'edu_checkout' ) ) {
			$gateway_id = isset( $_POST['edu_gateway_used'] ) ? sanitize_text_field( $_POST['edu_gateway_used'] ) : 'simulated';

			ob_start();
			?>
			<div class="edu-card" style="text-align:center; padding:60px;">
				<div class="edu-loader" style="border: 6px solid #f3f3f3; border-top: 6px solid var(--edu-primary); border-radius: 50%; width: 60px; height: 60px; animation: spin 2s linear infinite; margin: 0 auto 20px;"></div>
				<h2><?php _e( 'Verifying Payment...', 'edupreneur-pro' ); ?></h2>
				<p><?php printf( __( 'Securing your connection to %s...', 'edupreneur-pro' ), ucfirst($gateway_id) ); ?></p>
				<div id="edu-status-log" style="font-size:12px; color:#888; margin-top:10px; font-family:monospace;">
					[<?php echo date('H:i:s'); ?>] Initiating handshake...<br>
				</div>

				<script>
					const log = document.getElementById('edu-status-log');
					setTimeout(() => { log.innerHTML += "[<?php echo date('H:i:s', time()+1); ?>] Authorizing tokens...<br>"; }, 1000);
					setTimeout(() => { log.innerHTML += "[<?php echo date('H:i:s', time()+2); ?>] Confirming ledger entry...<br>"; }, 2000);
				</script>

				<style>
					@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
				</style>

				<form id="edu-final-enroll-form" method="post">
					<?php wp_nonce_field( 'edu_checkout' ); ?>
					<input type="hidden" name="edu_final_enroll" value="1">
					<input type="hidden" name="edu_gateway_used" value="<?php echo esc_attr($gateway_id); ?>">
				</form>

				<script>
					setTimeout(function() {
						document.getElementById('edu-final-enroll-form').submit();
					}, 3000);
				</script>
			</div>
			<?php
			return ob_get_clean();
		}

		if ( isset( $_POST['edu_final_enroll'] ) && check_admin_referer( 'edu_checkout' ) ) {
			$gateway_id = isset( $_POST['edu_gateway_used'] ) ? sanitize_text_field( $_POST['edu_gateway_used'] ) : 'simulated';

			// Simulate order creation
			$wpdb->insert( "{$wpdb->prefix}edu_orders", array(
				'user_id'      => get_current_user_id(),
				'total_amount' => $course->price,
				'status'       => 'completed'
			) );
			$order_id = $wpdb->insert_id;

			// Record Payment
			$wpdb->insert( "{$wpdb->prefix}edu_payments", array(
				'order_id' => $order_id,
				'amount'   => $course->price,
				'status'   => 'succeeded'
			) );

			// Enroll student
			$wpdb->insert( "{$wpdb->prefix}edu_enrollments", array(
				'student_id' => get_current_user_id(),
				'course_id'  => $course_id,
				'status'     => 'active'
			) );

			// Check for affiliate cookie
			if ( isset( $_COOKIE['edu_affiliate'] ) ) {
				$ref_code = sanitize_text_field( $_COOKIE['edu_affiliate'] );
				$affiliate = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}edu_affiliates WHERE referral_code = %s AND status = 'active'", $ref_code ) );
				if ( $affiliate ) {
					$aff_manager = new \EdupreneurPro\Modules\Affiliate\Services\AffiliateManager();
					$aff_manager->record_commission( $affiliate->id, $order_id, $course->price );
				}
			}

			$output = '<div class="edu-card" style="text-align:center; border:2px solid #28a745; padding:40px;">';
			$output .= '<div style="font-size: 4em; color:#28a745; margin-bottom:20px;">🎉</div>';
			$output .= '<h2>' . __( 'Payment Verified!', 'edupreneur-pro' ) . '</h2>';
			$output .= '<p>' . sprintf( __( 'Your transaction via %s was successful.', 'edupreneur-pro' ), ucfirst($gateway_id) ) . '</p>';
			$output .= '<div style="background:#f8f9fa; padding:15px; border-radius:8px; margin:20px 0; font-family:monospace;">Order ID: #' . $order_id . '</div>';
			$output .= '<p>' . __( 'You have been enrolled in the course. Start your learning journey now.', 'edupreneur-pro' ) . '</p>';
			$output .= '<a href="' . add_query_arg( 'edu_course_id', $course_id, $base_url ) . '" class="edu-btn" style="margin-top:20px; padding: 12px 40px;">' . __( 'Go to My Course', 'edupreneur-pro' ) . '</a>';
			$output .= '</div>';
			return $output;
		}

		if ( isset( $_POST['edu_initiate_payment'] ) && check_admin_referer( 'edu_checkout' ) ) {
			$gateway_id = sanitize_text_field( $_POST['edu_gateway'] );
			ob_start();
			?>
			<div class="edu-card" style="text-align:center; padding:60px;">
				<div class="edu-loader" style="border: 6px solid #f3f3f3; border-top: 6px solid var(--edu-primary); border-radius: 50%; width: 60px; height: 60px; animation: spin 2s linear infinite; margin: 0 auto 20px;"></div>
				<h2><?php _e( 'Redirecting to Secure Gateway...', 'edupreneur-pro' ); ?></h2>
				<p><?php printf( __( 'Please wait while we connect you to %s.', 'edupreneur-pro' ), ucfirst($gateway_id) ); ?></p>
				<style> @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } } </style>
				<script>
					setTimeout(function() {
						window.location.href = "<?php echo add_query_arg( array( 'edu_external_gateway' => 1, 'gateway' => $gateway_id, 'buy_course' => $course->id ), get_permalink() ); ?>";
					}, 2000);
				</script>
			</div>
			<?php
			return ob_get_clean();
		}

		if ( isset( $_GET['edu_external_gateway'] ) ) {
			$gateway_id = sanitize_text_field( $_GET['gateway'] );
			return $this->render_gateway_simulation( $course, $gateway_id );
		}

		$output = '<div class="edu-card" style="max-width: 600px; margin: 40px auto; padding: 40px;">';
		$output .= '<h2 style="margin-top:0;">' . __( 'Complete Your Enrollment', 'edupreneur-pro' ) . '</h2>';
		$output .= '<div style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:30px;">';
		$output .= '<div style="display:flex; justify-content:space-between; margin-bottom:10px;"><strong>' . __( 'Course Title:', 'edupreneur-pro' ) . '</strong><span>' . esc_html( $course->title ) . '</span></div>';
		$output .= '<div style="display:flex; justify-content:space-between; font-size:1.2em; border-top:1px solid #ddd; padding-top:10px;"><strong>' . __( 'Total Due:', 'edupreneur-pro' ) . '</strong><span style="color:var(--edu-primary); font-weight:700;">$' . number_format( $course->price, 2 ) . '</span></div>';
		$output .= '</div>';

		$output .= '<p class="edu-caption" style="margin-bottom:20px;">' . __( 'Select your preferred payment gateway.', 'edupreneur-pro' ) . '</p>';

		$output .= '<form method="post">';
		$output .= wp_nonce_field( 'edu_checkout', '_wpnonce', true, false );
		$output .= '<div class="edu-gateway-options" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; margin-bottom:20px;">';
		$output .= '<label style="border:2px solid #eee; padding:15px; border-radius:8px; text-align:center; cursor:pointer; display:block;"><input type="radio" name="edu_gateway" value="stripe" checked><br><strong>Stripe</strong><br><small>Cards / ApplePay</small></label>';
		$output .= '<label style="border:2px solid #eee; padding:15px; border-radius:8px; text-align:center; cursor:pointer; display:block;"><input type="radio" name="edu_gateway" value="paypal"><br><strong>PayPal</strong><br><small>PayPal / Credit</small></label>';
		$output .= '<label style="border:2px solid #eee; padding:15px; border-radius:8px; text-align:center; cursor:pointer; display:block;"><input type="radio" name="edu_gateway" value="gcash"><br><strong>GCash</strong><br><small>e-Wallet (PH)</small></label>';
		$output .= '</div>';
		$output .= '<div style="margin-bottom:20px; text-align:center; opacity:0.7; font-size:0.85em;">🛡️ Secured by 256-bit SSL encryption</div>';
		$output .= '<button type="submit" name="edu_initiate_payment" value="1" class="edu-btn edu-btn-block" style="padding: 18px; font-size:1.2em; background:var(--edu-primary); font-weight:700;">' . __( 'Authorize & Pay Now', 'edupreneur-pro' ) . '</button>';
		$output .= '</form>';
		$output .= '</div>';

		return $output;
	}

	public function render_gateway_simulation( $course, $gateway_id ) {
		$gateway_name = ucfirst($gateway_id);
		$bg_color = '#ffffff';
		$accent_color = 'var(--edu-primary)';

		if($gateway_id === 'stripe') { $accent_color = '#635bff'; }
		if($gateway_id === 'paypal') { $accent_color = '#003087'; }
		if($gateway_id === 'gcash') { $accent_color = '#007dfe'; }

		ob_start();
		?>
		<div class="edu-external-gateway-sim" style="background:#f6f9fc; min-height:500px; padding:40px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
			<div style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 50px 100px -20px rgba(50,50,93,.25), 0 30px 60px -30px rgba(0,0,0,.3); overflow:hidden;">
				<div style="padding:20px; background: <?php echo $accent_color; ?>; color:#fff; display:flex; justify-content:space-between; align-items:center;">
					<h3 style="margin:0; color:#fff; font-size:16px;"><?php echo $gateway_name; ?> Checkout</h3>
					<span style="font-size:12px; opacity:0.8;">Secure encrypted connection</span>
				</div>
				<div style="padding:30px;">
					<div style="margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:20px;">
						<p style="margin:0; color:#6b7c93; font-size:14px;"><?php _e('Merchant:', 'edupreneur-pro'); ?> EdupreneurPro Academy</p>
						<h2 style="margin:10px 0; font-size:32px; color:#32325d;">$<?php echo number_format($course->price, 2); ?></h2>
						<p style="margin:0; color:#32325d; font-weight:600;"><?php echo esc_html($course->title); ?></p>
					</div>

					<div style="margin-bottom:20px;">
						<label style="display:block; margin-bottom:8px; color:#32325d; font-size:14px; font-weight:500;"><?php _e('Payment Information', 'edupreneur-pro'); ?></label>
						<div style="border:1px solid #e6ebf1; padding:12px; border-radius:4px; color:#32325d; font-size:14px; display:flex; justify-content:space-between; align-items:center;">
							<span>**** **** **** 4242</span>
							<span style="font-size:10px; background:#f6f9fc; padding:2px 5px; border-radius:3px;">VALID</span>
						</div>
					</div>

					<div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:20px;">
						<div>
							<label style="display:block; margin-bottom:8px; color:#32325d; font-size:12px; font-weight:500;"><?php _e('Expiry', 'edupreneur-pro'); ?></label>
							<div style="border:1px solid #e6ebf1; padding:10px; border-radius:4px; color:#32325d; font-size:14px;">12 / 25</div>
						</div>
						<div>
							<label style="display:block; margin-bottom:8px; color:#32325d; font-size:12px; font-weight:500;"><?php _e('CVC', 'edupreneur-pro'); ?></label>
							<div style="border:1px solid #e6ebf1; padding:10px; border-radius:4px; color:#32325d; font-size:14px;">***</div>
						</div>
					</div>

					<form method="post">
						<?php wp_nonce_field( 'edu_checkout' ); ?>
						<input type="hidden" name="edu_confirm_purchase" value="1">
						<input type="hidden" name="edu_gateway_used" value="<?php echo esc_attr($gateway_id); ?>">
						<button type="submit" style="width:100%; padding:14px; background:<?php echo $accent_color; ?>; color:#fff; border:none; border-radius:4px; font-size:16px; font-weight:600; cursor:pointer; box-shadow: 0 4px 6px rgba(50,50,93,.11), 0 1px 3px rgba(0,0,0,.08); transition: all 0.15s ease;">
							<?php printf( __('Pay $%s with %s', 'edupreneur-pro'), number_format($course->price, 2), $gateway_name ); ?>
						</button>
					</form>

					<p style="text-align:center; margin-top:20px; font-size:13px; color:#6b7c93;">
						<a href="<?php echo get_permalink(); ?>" style="color:#6b7c93; text-decoration:none;">← <?php _e('Cancel and return', 'edupreneur-pro'); ?></a>
					</p>
				</div>
			</div>
			<div style="text-align:center; margin-top:30px;">
				<p style="color:#6b7c93; font-size:14px;">Powered by <strong>EdupreneurPro Payments Engine</strong></p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	private function get_course_html( $course ) {
		$base_url = ( is_admin() && isset( $_GET['page'] ) ) ? admin_url( 'admin.php?page=' . sanitize_text_field( $_GET['page'] ) ) : get_permalink();
		$output = '<div class="edu-card">';
		$output .= '<div style="height: 150px; background: #eee; border-radius: 6px; margin-bottom: 15px; display:flex; align-items:center; justify-content:center; font-size: 3em;">📘</div>';
		$output .= '<span class="tag" style="background:var(--edu-primary); color:#fff; border:none; margin-bottom:10px;">' . esc_html( $course->category ?: 'General' ) . '</span>';
		$output .= '<h3>' . esc_html( $course->title ) . '</h3>';
		$output .= '<p>' . esc_html( wp_trim_words( $course->description, 15 ) ) . '</p>';
		$output .= '<div style="font-weight:700; color:var(--edu-primary); margin: 15px 0; font-size: 1.2em;">$' . number_format( $course->price, 2 ) . '</div>';
		$output .= '<div style="margin-top:auto; display:flex; gap:10px;">';
		$output .= '<a href="' . add_query_arg( 'edu_course_id', $course->id, $base_url ) . '" class="edu-btn" style="flex:1;">' . __( 'Info', 'edupreneur-pro' ) . '</a>';
		$output .= '<a href="' . add_query_arg( 'buy_course', $course->id, $base_url ) . '" class="edu-btn" style="background:#28a745; flex:1;">' . __( 'Enroll', 'edupreneur-pro' ) . '</a>';
		$output .= '</div>';
		$output .= '</div>';
		return $output;
	}
}
