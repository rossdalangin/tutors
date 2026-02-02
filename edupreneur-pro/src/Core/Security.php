<?php

namespace EdupreneurPro\Core;

/**
 * Security Class
 */
class Security {

	/**
	 * Verify nonce.
	 */
	public static function verify_nonce( $nonce, $action ) {
		return wp_verify_nonce( $nonce, $action );
	}

	/**
	 * Sanitize array data.
	 */
	public static function sanitize_array( $data ) {
		$sanitized = array();
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$sanitized[ $key ] = self::sanitize_array( $value );
			} else {
				$sanitized[ $key ] = sanitize_text_field( $value );
			}
		}
		return $sanitized;
	}
}
