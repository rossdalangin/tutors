<?php

namespace EdupreneurPro\Modules\Dashboard\Services;

/**
 * Analytics Engine Class
 */
class AnalyticsEngine {

	/**
	 * Get total revenue stats.
	 *
	 * @return array
	 */
	public function get_revenue_stats() {
		$stats = get_transient( 'edu_revenue_stats' );
		if ( $stats !== false ) {
			return $stats;
		}

		global $wpdb;

		$total_gross = $wpdb->get_var( "SELECT SUM(total_amount) FROM {$wpdb->prefix}edu_orders WHERE status = 'completed'" );
		$total_refunds = $wpdb->get_var( "SELECT SUM(total_amount) FROM {$wpdb->prefix}edu_orders WHERE status = 'refunded'" );
		$net_revenue = $total_gross - $total_refunds;

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}edu_orders WHERE status = 'completed'" );
		$students = $wpdb->get_var( "SELECT COUNT(DISTINCT student_id) FROM {$wpdb->prefix}edu_enrollments" );
		$avg_completion = $wpdb->get_var( "SELECT AVG(completed) FROM {$wpdb->prefix}edu_progress" ) * 100;

		$stats = array(
			'gross'           => $total_gross ?: 0,
			'refunds'         => $total_refunds ?: 0,
			'net'             => $net_revenue ?: 0,
			'order_count'     => $count ?: 0,
			'student_count'   => $students ?: 0,
			'avg_completion'  => round( $avg_completion ?: 0, 1 ),
			'avg_order_value' => $count ? ( $total_gross / $count ) : 0,
		);

		set_transient( 'edu_revenue_stats', $stats, HOUR_IN_SECONDS );
		return $stats;
	}

	/**
	 * Get revenue by period for tax-ready reports.
	 *
	 * @param string $start_date
	 * @param string $end_date
	 * @return array
	 */
	public function get_revenue_by_period( $start_date, $end_date ) {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT DATE(created_at) as date, SUM(total_amount) as amount
			FROM {$wpdb->prefix}edu_orders
			WHERE status = 'completed' AND created_at BETWEEN %s AND %s
			GROUP BY DATE(created_at) ORDER BY date ASC",
			$start_date,
			$end_date
		) );
	}

	/**
	 * Get course performance metrics.
	 *
	 * @param int $course_id
	 * @return array
	 */
	public function get_course_metrics( $course_id ) {
		global $wpdb;

		$total_students = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}edu_enrollments WHERE course_id = %d",
			$course_id
		) );

		$completions = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(DISTINCT student_id) FROM {$wpdb->prefix}edu_progress
			WHERE course_id = %d AND completed = 1",
			$course_id
		) );

		// This is a simplified completion rate logic
		$completion_rate = $total_students > 0 ? ( $completions / $total_students ) * 100 : 0;

		return array(
			'total_students'  => $total_students ?: 0,
			'completions'     => $completions ?: 0,
			'completion_rate' => round( $completion_rate, 2 ),
		);
	}
}
