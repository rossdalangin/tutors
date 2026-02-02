<?php
namespace EdupreneurPro\Core;
class Container {
	private $registry = array();
	public function set( $key, $value ) { $this->registry[ $key ] = $value; }
	public function get( $key ) { return isset( $this->registry[ $key ] ) ? $this->registry[ $key ] : null; }
	public function has( $key ) { return isset( $this->registry[ $key ] ); }
}
