<?php
namespace EdupreneurPro\Modules\Affiliate\Services;

class AffiliateDashboard {
	public function get_stats( $user_id ) {
		global $wpdb;
		$affiliate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_affiliates WHERE user_id = %d", $user_id ) );
		if ( ! $affiliate ) return array();
		$earnings = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(amount) FROM {$wpdb->prefix}edu_commissions WHERE affiliate_id = %d", $affiliate->id ) );
		$conversions = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}edu_commissions WHERE affiliate_id = %d", $affiliate->id ) );

		return array(
			'referral_code'  => $affiliate->referral_code,
			'referral_link'  => add_query_arg( 'ref', $affiliate->referral_code, home_url( '/' ) ),
			'total_earnings' => $earnings ?: 0,
			'conversions'    => $conversions ?: 0,
		);
	}
}
