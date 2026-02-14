<?php
namespace EdupreneurPro\Core;

class PageService {

	public static function get_pages_to_create() {
		return array(
			'edu_home_page' => array(
				'title'   => __( 'Academy Home', 'edupreneur-pro' ),
				'content' => '[edu_homepage]',
			),
			'edu_dashboard_page' => array(
				'title'   => __( 'Student Dashboard', 'edupreneur-pro' ),
				'content' => '[edu_student_dashboard]',
			),
			'edu_checkout_page' => array(
				'title'   => __( 'Secure Checkout', 'edupreneur-pro' ),
				'content' => '[edu_checkout]',
			),
			'edu_messages_page' => array(
				'title'   => __( 'My Messages', 'edupreneur-pro' ),
				'content' => '[edu_messages]',
			),
			'edu_directory_page' => array(
				'title'   => __( 'Member Directory', 'edupreneur-pro' ),
				'content' => '[edu_directory]',
			),
			'edu_community_page' => array(
				'title'   => __( 'Community Discussion', 'edupreneur-pro' ),
				'content' => '[edu_student_community]',
			),
		);
	}

	public static function create_pages() {
		$pages = self::get_pages_to_create();
		$created_count = 0;

		foreach ( $pages as $option_key => $page_data ) {
			$existing_page_id = get_option( $option_key );

			if ( $existing_page_id && get_post( $existing_page_id ) ) {
				continue;
			}

			$page_id = wp_insert_post( array(
				'post_title'   => $page_data['title'],
				'post_content' => $page_data['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			) );

			if ( $page_id ) {
				update_option( $option_key, $page_id );
				$created_count++;
			}
		}

		return $created_count;
	}
}
