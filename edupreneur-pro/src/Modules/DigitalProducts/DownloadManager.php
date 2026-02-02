<?php
namespace EdupreneurPro\Modules\DigitalProducts;

class DownloadManager {
	public function generate_download_url( $product_id, $user_id ) {
		$token = wp_generate_password( 32, false );
		set_transient( 'edu_download_' . $token, array( 'product_id' => $product_id, 'user_id' => $user_id ), HOUR_IN_SECONDS );
		return add_query_arg( 'edu_download', $token, home_url( '/' ) );
	}
}
