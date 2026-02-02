<?php
/**
 * Plugin Name: EdupreneurPro
 * @package EdupreneurPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Fallback Autoloader.
spl_autoload_register( function ( $class ) {
	$prefix = 'EdupreneurPro\\';
	$base_dir = __DIR__ . '/src/';
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}
	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
	if ( file_exists( $file ) ) {
		require $file;
	}
} );

final class EdupreneurPro {
	private static $instance;
	public $container;

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new EdupreneurPro();
			self::$instance->setup();
		}
		return self::$instance;
	}

	private function setup() {
		$this->define_constants();
		$this->init_container();
		$this->init_modules();
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'init', array( $this, 'track_affiliate_referral' ) );
		add_action( 'admin_init', array( $this, 'ensure_admin_capabilities' ) );
		add_shortcode( 'edu_student_dashboard', array( $this, 'render_student_dashboard' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	/**
	 * Ensure admin has necessary capabilities.
	 */
	public function ensure_admin_capabilities() {
		if ( current_user_can( 'administrator' ) && ! current_user_can( 'manage_edu_courses' ) ) {
			\EdupreneurPro\Core\Auth\Roles::grant_admin_caps();
		}
	}

	/**
	 * Render student dashboard shortcode.
	 */
	public function render_student_dashboard() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view your courses.', 'edupreneur-pro' ) . '</p>';
		}

		global $wpdb;
		$student_id = get_current_user_id();
		$courses = $wpdb->get_results( $wpdb->prepare(
			"SELECT c.* FROM {$wpdb->prefix}edu_courses c
			JOIN {$wpdb->prefix}edu_enrollments e ON c.id = e.course_id
			WHERE e.student_id = %d AND e.status = 'active'",
			$student_id
		) );

		ob_start();
		echo '<div class="edu-student-dashboard">';
		echo '<h2>' . esc_html__( 'My Courses', 'edupreneur-pro' ) . '</h2>';
		echo '<p>' . esc_html__( 'Welcome back! Here are the courses you are currently enrolled in.', 'edupreneur-pro' ) . '</p>';

		if ( empty( $courses ) ) {
			echo '<div class="edu-grid"><div class="edu-card"><p>' . esc_html__( 'No courses found.', 'edupreneur-pro' ) . '</p></div></div>';
		} else {
			echo '<div class="edu-grid">';
			foreach ( $courses as $course ) {
				echo '<div class="edu-card">';
				echo '<h3>' . esc_html( $course->title ) . '</h3>';
				echo '<p>' . esc_html( wp_trim_words( $course->description, 20 ) ) . '</p>';
				echo '</div>';
			}
			echo '</div>';
		}
		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Track affiliate referral.
	 */
	public function track_affiliate_referral() {
		if ( isset( $_GET['ref'] ) ) {
			setcookie( 'edu_affiliate', sanitize_text_field( $_GET['ref'] ), time() + ( 30 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN );
		}
	}

	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'edu' ) !== false || strpos( $hook, 'edupreneur' ) !== false ) {
			wp_enqueue_style( 'edu-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', array(), EDUPRENEUR_PRO_VERSION );
		}
	}

	private function define_constants() {
		define( 'EDUPRENEUR_PRO_VERSION', '1.0.0' );
		define( 'EDUPRENEUR_PRO_PATH', plugin_dir_path( __FILE__ ) );
	}

	private function init_container() {
		$this->container = new \EdupreneurPro\Core\Container();
	}

	private function init_modules() {
		$module_manager = new \EdupreneurPro\Core\Modules\ModuleManager( $this->container );
		$this->container->set( 'modules', $module_manager );
		$module_manager->init();
	}

	public function activate() {
		\EdupreneurPro\Core\Database\Migrations::run();
		\EdupreneurPro\Core\Auth\Roles::register();
		flush_rewrite_rules();
	}

	public function on_plugins_loaded() {}
}

function edupreneur_pro_init() {
	return EdupreneurPro::instance();
}
edupreneur_pro_init();
