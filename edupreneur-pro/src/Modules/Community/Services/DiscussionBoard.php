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

		if ( empty( $data['content'] ) ) {
			return false;
		}

		$data['content'] = wp_kses_post( $data['content'] );
		$data['user_id'] = get_current_user_id();

		return $wpdb->insert( $this->table, $data );
	}

	public function pin_post( $post_id ) {
		global $wpdb;
		return $wpdb->update( $this->table, array( 'is_pinned' => 1 ), array( 'id' => $post_id ) );
	}

	public function delete_post( $post_id ) {
		global $wpdb;
		return $wpdb->delete( $this->table, array( 'id' => $post_id ) );
	}
}
