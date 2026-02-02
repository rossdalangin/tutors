<?php
namespace EdupreneurPro\Modules\Community\Services;

class DiscussionBoard {
	private $table;
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'edu_community_posts';
	}
	public function get_posts( $course_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE course_id = %d ORDER BY created_at DESC", $course_id ) );
	}
	public function create_post( $data ) {
		global $wpdb;
		return $wpdb->insert( $this->table, $data );
	}
}
