<?php
namespace EdupreneurPro\Modules\Payments\Repositories;

class PaymentRepository {
	private $table;
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'edu_payments';
	}
	public function create( $data ) {
		global $wpdb;
		return $wpdb->insert( $this->table, $data ) ? $wpdb->insert_id : false;
	}
}
