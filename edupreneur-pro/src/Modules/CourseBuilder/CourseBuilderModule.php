<?php

namespace EdupreneurPro\Modules\CourseBuilder;

use EdupreneurPro\Core\Modules\ModuleInterface;
use EdupreneurPro\Core\Container;
use EdupreneurPro\Modules\CourseBuilder\Controllers\CourseController;
use EdupreneurPro\Modules\CourseBuilder\Controllers\LessonController;
use EdupreneurPro\Modules\CourseBuilder\Admin\CourseAdmin;

class CourseBuilderModule implements ModuleInterface {

	private $container;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		if ( is_admin() ) {
			$admin = new CourseAdmin();
			$admin->init();
		}
	}

	public function register_routes() {
		$course_controller = new CourseController();
		$course_controller->register_routes();

		$lesson_controller = new LessonController();
		$lesson_controller->register_routes();
	}

	public function get_id() {
		return 'course-builder';
	}
}
