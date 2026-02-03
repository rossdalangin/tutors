<?php
namespace EdupreneurPro\Modules\Community\Services;

class Messaging {
	private $table;
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'edu_community_posts';
	}
	public function send_message( $sender_id, $receiver_id, $content ) {
		global $wpdb;
		$inserted = $wpdb->insert( $this->table, array(
			'user_id'      => $sender_id,
			'recipient_id' => $receiver_id,
			'course_id'    => 0,
			'content'      => wp_kses_post( $content ),
		) );
		if ( $inserted ) {
			return $wpdb->insert_id;
		}
		return false;
	}
}
