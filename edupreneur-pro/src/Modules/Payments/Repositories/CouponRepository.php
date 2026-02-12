<?php
namespace EdupreneurPro\Modules\Payments\Repositories;

class CouponRepository {
	private $table;
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'edu_coupons';
	}

	public function get_by_code( $code ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE code = %s", $code ) );
	}

	public function is_valid( $coupon ) {
		if ( ! $coupon ) return false;
		if ( $coupon->expiry_date && strtotime( $coupon->expiry_date ) < time() ) return false;
		if ( $coupon->usage_limit > 0 && $coupon->usage_count >= $coupon->usage_limit ) return false;
		return true;
	}

	public function increment_usage( $id ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "UPDATE {$this->table} SET usage_count = usage_count + 1 WHERE id = %d", $id ) );
	}
}
