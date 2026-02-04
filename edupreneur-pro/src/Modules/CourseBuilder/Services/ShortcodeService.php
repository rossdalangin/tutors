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

			// Simulate order creation
			$wpdb->insert( "{$wpdb->prefix}edu_orders", array(
				'user_id'      => get_current_user_id(),
				'total_amount' => $course->price,
				'status'       => 'completed'
			) );
			$order_id = $wpdb->insert_id;

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
			$output .= '<h2>' . __( 'Registration Successful!', 'edupreneur-pro' ) . '</h2>';
			$output .= '<p>' . __( 'You have been enrolled in the course. Start your learning journey now.', 'edupreneur-pro' ) . '</p>';
			$output .= '<a href="' . add_query_arg( 'edu_course_id', $course_id, $base_url ) . '" class="edu-btn" style="margin-top:20px; padding: 12px 40px;">' . __( 'Go to My Course', 'edupreneur-pro' ) . '</a>';
			$output .= '</div>';
			return $output;
		}

		if ( isset( $_POST['edu_initiate_payment'] ) && check_admin_referer( 'edu_checkout' ) ) {
			$gateway_id = sanitize_text_field( $_POST['edu_gateway'] );
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
		$output .= '<label style="border:1px solid #ddd; padding:15px; border-radius:8px; text-align:center; cursor:pointer; display:block;"><input type="radio" name="edu_gateway" value="stripe" checked><br>Stripe</label>';
		$output .= '<label style="border:1px solid #ddd; padding:15px; border-radius:8px; text-align:center; cursor:pointer; display:block;"><input type="radio" name="edu_gateway" value="paypal"><br>PayPal</label>';
		$output .= '<label style="border:1px solid #ddd; padding:15px; border-radius:8px; text-align:center; cursor:pointer; display:block;"><input type="radio" name="edu_gateway" value="gcash"><br>GCash</label>';
		$output .= '</div>';
		$output .= '<button type="submit" name="edu_initiate_payment" value="1" class="edu-btn edu-btn-block" style="padding: 15px; font-size:1.1em; background:var(--edu-primary);">' . __( 'Proceed to Payment', 'edupreneur-pro' ) . '</button>';
		$output .= '</form>';
		$output .= '</div>';

		return $output;
	}

	public function render_gateway_simulation( $course, $gateway_id ) {
		$output = '<div class="edu-card" style="max-width: 600px; margin: 40px auto; text-align:center; padding:40px;">';
		$output .= '<div style="font-size: 3em; margin-bottom: 20px;">🏦</div>';
		$output .= '<h2>' . sprintf( __( 'Simulated %s Gateway', 'edupreneur-pro' ), ucfirst($gateway_id) ) . '</h2>';
		$output .= '<p>' . sprintf( __( 'You have been redirected to the secure %s payment page.', 'edupreneur-pro' ), ucfirst($gateway_id) ) . '</p>';
		$output .= '<div style="background:#f0f0f1; padding:20px; border-radius:8px; margin:20px 0; border: 1px dashed #ccc;">';
		$output .= '<p style="margin:0; color:#666;">' . __( 'Payment Reference:', 'edupreneur-pro' ) . ' EDU-' . time() . '</p>';
		$output .= '<div style="font-size:2em; font-weight:700; color:var(--edu-text); margin-top:10px;">$' . number_format($course->price, 2) . '</div>';
		$output .= '</div>';
		$output .= '<form method="post">';
		$output .= wp_nonce_field( 'edu_checkout', '_wpnonce', true, false );
		$output .= '<input type="hidden" name="edu_confirm_purchase" value="1">';
		$output .= '<input type="hidden" name="edu_gateway_used" value="' . esc_attr($gateway_id) . '">';
		$output .= '<button type="submit" class="edu-btn edu-btn-block" style="padding: 15px; font-size:1.1em; background:#28a745;">' . __( 'Authorize & Pay Now', 'edupreneur-pro' ) . '</button>';
		$output .= '<p style="margin-top:15px; font-size:0.9em;"><a href="' . get_permalink() . '">' . __( 'Cancel and return to site', 'edupreneur-pro' ) . '</a></p>';
		$output .= '</form>';
		$output .= '</div>';
		return $output;
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
