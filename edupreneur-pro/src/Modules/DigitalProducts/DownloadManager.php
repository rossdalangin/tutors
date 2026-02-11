<?php
namespace EdupreneurPro\Modules\DigitalProducts;

class DownloadManager {
	public function generate_download_url( $product_id, $user_id ) {
		$token = wp_generate_password( 32, false );
		set_transient( 'edu_download_' . $token, array( 'product_id' => $product_id, 'user_id' => $user_id ), HOUR_IN_SECONDS );
		return add_query_arg( 'edu_download', $token, home_url( '/' ) );
	}

	/**
	 * Verify a download token.
	 */
	public function verify_token( $token ) {
		$data = get_transient( 'edu_download_' . $token );
		if ( ! $data ) return false;

		// Check download limits
		$count = get_user_meta( $data['user_id'], '_edu_download_count_' . $data['product_id'], true ) ?: 0;
		if ( $count >= 5 ) { // Hardcoded limit for demo
			return false;
		}

		update_user_meta( $data['user_id'], '_edu_download_count_' . $data['product_id'], $count + 1 );
		return $data;
	}

	public function watermark_pdf( $file_path, $text ) {
		// Mock implementation: In production, use FPDM or SetaPDF
		error_log( sprintf( 'Digital Product Protection: Applying watermark [%s] to %s', $text, $file_path ) );

		// In a real environment, we'd return a path to a temporary watermarked file
		return $file_path . '?watermarked=' . urlencode( $text );
	}

	/**
	 * Serve a protected file.
	 */
	public function serve_protected_file( $product_id, $user_id ) {
		global $wpdb;
		$product = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_products WHERE id = %d", $product_id ) );
		if ( ! $product ) return;

		$user = get_userdata( $user_id );
		$watermark_text = "Licensed to: " . $user->user_email . " | Transaction ID: " . wp_generate_password(8, false);

		$protected_url = $this->watermark_pdf( $product->file_url, $watermark_text );

		// Redirect to the "protected" URL (simulation)
		wp_redirect( $protected_url );
		exit;
	}
}
