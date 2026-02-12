<?php
namespace EdupreneurPro\Modules\Affiliate\Services;

use EdupreneurPro\Modules\Affiliate\Repositories\AffiliateRepository;

class AffiliateManager {
	private $repository;
	public function __construct() {
		$this->repository = new AffiliateRepository();
	}
	public function register_affiliate( $user_id ) {
		global $wpdb;
		$ip = $_SERVER['REMOTE_ADDR'];

		// Anti-fraud: IP duplication check
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}edu_affiliates WHERE ip_address = %s", $ip ) );
		if ( $existing ) {
			return false; // Already registered from this IP
		}

		return $this->repository->create( array(
			'user_id'       => $user_id,
			'referral_code' => wp_generate_password( 8, false ),
			'status'        => 'active',
			'ip_address'    => $ip
		) );
	}

	public function record_commission( $affiliate_id, $order_id, $amount, $is_recurring = false ) {
		global $wpdb;

		// Anti-fraud: Check if student is the affiliate themselves
		$order = $wpdb->get_row( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}edu_orders WHERE id = %d", $order_id ) );
		$affiliate = $wpdb->get_row( $wpdb->prepare( "SELECT user_id, commission_rate FROM {$wpdb->prefix}edu_affiliates WHERE id = %d", $affiliate_id ) );

		if ( $order && $affiliate && $order->user_id === $affiliate->user_id ) {
			return false; // Self-referral blocked
		}

		// Per-product override check (simplified logic)
		$override_rate = get_option( 'edu_affiliate_override_rate', 0 );
		$rate = $override_rate > 0 ? $override_rate : $affiliate->commission_rate;

		$commission_amount = $amount * ( $rate / 100 );

		return $wpdb->insert( "{$wpdb->prefix}edu_commissions", array(
			'affiliate_id' => $affiliate_id,
			'order_id'     => $order_id,
			'amount'       => $commission_amount,
			'status'       => 'unpaid',
			'created_at'   => current_time( 'mysql' )
		) );
	}

	public function process_subscription_renewal( $subscription_id ) {
		global $wpdb;
		$sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_subscriptions WHERE id = %d", $subscription_id ) );
		if ( ! $sub ) return;

		$course = $wpdb->get_row( $wpdb->prepare( "SELECT price FROM {$wpdb->prefix}edu_courses WHERE id = %d", $sub->course_id ) );

		// Create new order
		$wpdb->insert( "{$wpdb->prefix}edu_orders", array(
			'user_id'      => $sub->user_id,
			'total_amount' => $course->price,
			'status'       => 'completed'
		) );
		$order_id = $wpdb->insert_id;

		// Update sub
		$wpdb->update( "{$wpdb->prefix}edu_subscriptions", array(
			'next_billing_at' => date( 'Y-m-d H:i:s', strtotime( '+1 ' . $sub->billing_period ) )
		), array( 'id' => $sub->id ) );

		// Find affiliate and record recurring commission
		$aff_id = $wpdb->get_var( $wpdb->prepare( "SELECT affiliate_id FROM {$wpdb->prefix}edu_commissions WHERE order_id = (SELECT id FROM {$wpdb->prefix}edu_orders WHERE user_id = %d AND status = 'completed' ORDER BY created_at ASC LIMIT 1)", $sub->user_id ) );
		if ( $aff_id ) {
			$this->record_commission( $aff_id, $order_id, $course->price, true );
		}
	}
}
