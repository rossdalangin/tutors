<?php
namespace EdupreneurPro\Modules\CourseBuilder\Services;

class ShortcodeService {
	public function init() {
		add_shortcode( 'edu_course', array( $this, 'render_course_card' ) );
		add_shortcode( 'edu_recent_courses', array( $this, 'render_recent_courses' ) );
		add_shortcode( 'edu_categories', array( $this, 'render_categories' ) );
		add_shortcode( 'edu_homepage', array( $this, 'render_homepage' ) );
		add_shortcode( 'edu_checkout', array( $this, 'render_checkout' ) );
	}

	public function render_course_card( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts );
		global $wpdb;
		$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_courses WHERE id = %d", $atts['id'] ) );
		if ( ! $course ) return '';

		return $this->get_course_html( $course );
	}

	public function render_recent_courses() {
		global $wpdb;
		$courses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}edu_courses WHERE status = 'publish' ORDER BY created_at DESC LIMIT 10" );

		$output = '<div class="edu-grid">';
		foreach ( $courses as $course ) {
			$output .= $this->get_course_html( $course );
		}
		$output .= '</div>';
		return $output;
	}

	public function render_categories() {
		global $wpdb;
		$categories = $wpdb->get_results( "SELECT DISTINCT category FROM {$wpdb->prefix}edu_courses WHERE status = 'publish'" );
		$base_url = ( is_admin() && isset( $_GET['page'] ) ) ? admin_url( 'admin.php?page=' . sanitize_text_field( $_GET['page'] ) ) : get_permalink();

		$output = '<div class="edu-grid">';
		foreach ( $categories as $cat ) {
			$cat_name = $cat->category ?: 'General';
			$output .= '<div class="edu-card">';
			$output .= '<h3>' . esc_html( $cat_name ) . '</h3>';
			$output .= '<p>' . sprintf( __( 'Explore all our %s courses.', 'edupreneur-pro' ), esc_html( $cat_name ) ) . '</p>';
			$output .= '<a href="' . add_query_arg( 'edu_category', $cat_name, $base_url ) . '" class="edu-btn">' . __( 'View Category', 'edupreneur-pro' ) . '</a>';
			$output .= '</div>';
		}
		$output .= '</div>';
		return $output;
	}

	public function render_homepage() {
		$output = '<div class="edu-homepage-hero" style="text-align:center; padding: 50px 20px; background: #f0f4f8; border-radius: 12px; margin-bottom: 40px;">';
		$output .= '<h1>' . __( 'Master New Skills with EdupreneurPro', 'edupreneur-pro' ) . '</h1>';
		$output .= '<p style="font-size: 1.2em;">' . __( 'The ultimate platform for professional learning and growth.', 'edupreneur-pro' ) . '</p>';
		$output .= '</div>';

		$output .= '<h2>' . __( 'Featured Courses', 'edupreneur-pro' ) . '</h2>';
		$output .= $this->render_recent_courses();

		$output .= '<h2 style="margin-top:40px;">' . __( 'Browse by Category', 'edupreneur-pro' ) . '</h2>';
		$output .= $this->render_categories();

		return $output;
	}

	public function render_checkout() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . __( 'Please log in to complete your purchase.', 'edupreneur-pro' ) . '</p>';
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

			return '<div class="updated"><p>' . __( 'Purchase successful! You are now enrolled.', 'edupreneur-pro' ) . '</p><a href="' . $base_url . '" class="edu-btn">' . __( 'Go to Dashboard', 'edupreneur-pro' ) . '</a></div>';
		}

		$output = '<div class="edu-card" style="max-width: 500px; margin: 0 auto;">';
		$output .= '<h2>' . __( 'Secure Checkout', 'edupreneur-pro' ) . '</h2>';
		$output .= '<p><strong>' . __( 'Course:', 'edupreneur-pro' ) . '</strong> ' . esc_html( $course->title ) . '</p>';
		$output .= '<p><strong>' . __( 'Price:', 'edupreneur-pro' ) . '</strong> $' . number_format( $course->price, 2 ) . '</p>';

		$output .= '<form method="post">';
		$output .= wp_nonce_field( 'edu_checkout', '_wpnonce', true, false );
		$output .= '<input type="hidden" name="edu_confirm_purchase" value="1">';
		$output .= '<button type="submit" class="edu-btn edu-btn-block">' . __( 'Complete Purchase (Simulated)', 'edupreneur-pro' ) . '</button>';
		$output .= '</form>';
		$output .= '</div>';

		return $output;
	}

	private function get_course_html( $course ) {
		$base_url = ( is_admin() && isset( $_GET['page'] ) ) ? admin_url( 'admin.php?page=' . sanitize_text_field( $_GET['page'] ) ) : get_permalink();
		$output = '<div class="edu-card">';
		$output .= '<h3>' . esc_html( $course->title ) . '</h3>';
		$output .= '<p>' . esc_html( wp_trim_words( $course->description, 15 ) ) . '</p>';
		$output .= '<div style="margin-top:15px; display:flex; gap:10px;">';
		$output .= '<a href="' . add_query_arg( 'edu_course_id', $course->id, $base_url ) . '" class="edu-btn">' . __( 'Sales Page', 'edupreneur-pro' ) . '</a>';
		$output .= '<a href="' . add_query_arg( 'buy_course', $course->id, $base_url ) . '" class="edu-btn" style="background:#28a745;">' . __( 'Buy Now', 'edupreneur-pro' ) . '</a>';
		$output .= '</div>';
		$output .= '</div>';
		return $output;
	}
}
