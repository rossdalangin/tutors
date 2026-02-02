<?php
use PHPUnit\Framework\TestCase;
use EdupreneurPro\Core\Container;

class CoreTest extends TestCase {
	public function test_container() {
		$container = new Container();
		$container->set('test', 'value');
		$this->assertEquals('value', $container->get('test'));
	}
}
