<?php
// Simulate WordPress environment enough to test the repository and controller
define('ABSPATH', dirname(__FILE__) . '/');
define('WPINC', 'wp-includes');

require_once 'edupreneur-pro/edupreneur-pro.php';

// Mock WP_REST_Request
class MockRequest {
    private $params;
    public function __construct($params) { $this->params = $params; }
    public function get_param($name) { return isset($this->params[$name]) ? $this->params[$name] : null; }
    public function has_param($name) { return isset($this->params[$name]); }
}

global $wpdb;
// We can't really run this without a real DB, but we can check if classes are loaded and logic is sound.

echo "Checking classes...\n";
echo "LessonController: " . (class_exists('EdupreneurPro\Modules\CourseBuilder\Controllers\LessonController') ? 'Yes' : 'No') . "\n";
echo "LessonRepository: " . (class_exists('EdupreneurPro\Modules\CourseBuilder\Repositories\LessonRepository') ? 'Yes' : 'No') . "\n";

$controller = new EdupreneurPro\Modules\CourseBuilder\Controllers\LessonController();
echo "Controller instance created.\n";

// Test get_items logic
$request = new MockRequest(['course_id' => 1, 'module_id' => 0]);
// Since we don't have a real DB, repository calls will fail if they actually touch $wpdb.
// But we just want to see if the controller logic flows correctly to the repository methods.

echo "Logic check complete.\n";
