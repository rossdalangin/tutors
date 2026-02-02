<?php
namespace EdupreneurPro\Core\Database;
class Migrations {
	public static function run() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( Schema::get_schema() as $sql ) {
			dbDelta( $sql );
		}
	}
}
