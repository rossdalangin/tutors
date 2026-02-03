<?php
namespace EdupreneurPro\Modules\Affiliate\Services;

use EdupreneurPro\Modules\Affiliate\Repositories\AffiliateRepository;

class AffiliateManager {
	private $repository;
	public function __construct() {
		$this->repository = new AffiliateRepository();
	}
	public function register_affiliate( $user_id ) {
		return $this->repository->create( array(
			'user_id'       => $user_id,
			'referral_code' => wp_generate_password( 8, false ),
			'status'        => 'active',
		) );
	}

	public function record_commission( $affiliate_id, $order_id, $amount ) {
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
			'status'       => 'unpaid'
		) );
	}
}
