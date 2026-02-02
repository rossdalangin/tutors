<?php
namespace EdupreneurPro\Modules\Affiliate\Services;

class AffiliateDashboard {
	public function get_stats( $user_id ) {
		global $wpdb;
		$affiliate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_affiliates WHERE user_id = %d", $user_id ) );
		if ( ! $affiliate ) return array();
		return array(
			'referral_code' => $affiliate->referral_code,
			'referral_link' => add_query_arg( 'ref', $affiliate->referral_code, home_url( '/' ) ),
		);
	}
}
