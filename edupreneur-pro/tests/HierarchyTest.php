<?php
use PHPUnit\Framework\TestCase;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\CourseBuilder\Repositories\ModuleRepository;

class HierarchyTest extends TestCase {
	public function test_module_repository_exists() {
		$this->assertTrue( class_exists( 'EdupreneurPro\Modules\CourseBuilder\Repositories\ModuleRepository' ) );
	}
}
