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
		add_filter( 'the_content', array( $this, 'handle_lesson_display' ) );
		add_action( 'template_redirect', array( $this, 'handle_lesson_completion' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	/**
	 * Ensure roles have necessary capabilities.
	 */
	public function ensure_admin_capabilities() {
		if ( is_admin() && current_user_can( 'read' ) ) {
			\EdupreneurPro\Core\Auth\Roles::ensure_all_caps();
		}
	}

	/**
	 * Render student dashboard shortcode.
	 */
	public function handle_lesson_completion() {
		if ( isset( $_POST['lesson_to_complete'] ) && check_admin_referer( 'edu_complete_lesson' ) ) {
			$lesson_id = intval( $_POST['lesson_to_complete'] );
			$progress = new \EdupreneurPro\Modules\CourseBuilder\Services\ProgressService();
			$progress->mark_lesson_complete( get_current_user_id(), $lesson_id );

			wp_safe_redirect( add_query_arg( array( 'edu_lesson' => $lesson_id, 'completed' => 1 ), wp_get_referer() ) );
			exit;
		}
	}

	public function handle_lesson_display( $content ) {
		if ( ! isset( $_GET['edu_lesson'] ) ) {
			return $content;
		}

		$lesson_id = intval( $_GET['edu_lesson'] );
		global $wpdb;
		$lesson = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_lessons WHERE id = %d", $lesson_id ) );

		if ( ! $lesson ) {
			return $content;
		}

		// Authorization check
		if ( ! current_user_can( 'read' ) ) {
			return '<p>' . esc_html__( 'Please log in to access this lesson.', 'edupreneur-pro' ) . '</p>';
		}

		// Progress Service Check (Drip)
		$progress = new \EdupreneurPro\Modules\CourseBuilder\Services\ProgressService();
		if ( ! $progress->can_access_lesson( get_current_user_id(), $lesson_id ) ) {
			return '<div class="edu-card edu-warning"><h3>' . esc_html__( 'Lesson Locked', 'edupreneur-pro' ) . '</h3><p>' . esc_html__( 'This lesson is not yet available based on your enrollment drip schedule.', 'edupreneur-pro' ) . '</p></div>';
		}

		ob_start();
		echo '<div class="edu-lesson-player">';
		echo '<a href="' . get_permalink() . '" class="edu-btn edu-btn-small" style="margin-bottom:20px;">' . esc_html__( '← Back to Dashboard', 'edupreneur-pro' ) . '</a>';
		echo '<h1>' . esc_html( $lesson->title ) . '</h1>';

		if ( ! empty( $lesson->video_url ) ) {
			echo '<div class="edu-video-container" style="margin: 20px 0; background: #000; aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center; color: #fff;">';
			echo '<p>Video Player: ' . esc_url( $lesson->video_url ) . '</p>';
			echo '</div>';
		}

		echo '<div class="edu-lesson-content">' . wpautop( $lesson->content ) . '</div>';

		$resources = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_resources WHERE lesson_id = %d", $lesson_id ) );
		if ( ! empty( $resources ) ) {
			echo '<div class="edu-card" style="margin-top:30px;"><h3>' . esc_html__( 'Learning Resources', 'edupreneur-pro' ) . '</h3><ul>';
			foreach ( $resources as $res ) {
				echo '<li><a href="' . esc_url( $res->url ) . '" target="_blank">' . esc_html( $res->title ) . '</a></li>';
			}
			echo '</ul></div>';
		}

		// Mark as complete button
		echo '<form method="post" style="margin-top:30px;">';
		wp_nonce_field( 'edu_complete_lesson' );
		echo '<input type="hidden" name="lesson_to_complete" value="' . $lesson_id . '">';
		echo '<button type="submit" class="edu-btn">' . esc_html__( 'Mark as Completed', 'edupreneur-pro' ) . '</button>';
		echo '</form>';

		echo '</div>';
		return ob_get_clean();
	}

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
		echo '<h2>' . esc_html__( 'My Learning Journey', 'edupreneur-pro' ) . '</h2>';
		echo '<p>' . esc_html__( 'Welcome back, learner! Continue where you left off and achieve your educational goals.', 'edupreneur-pro' ) . '</p>';

		if ( empty( $courses ) ) {
			echo '<div class="edu-grid"><div class="edu-card" style="grid-column: 1/-1;"><p>' . esc_html__( 'You haven\'t enrolled in any courses yet. Explore our catalog to start learning!', 'edupreneur-pro' ) . '</p></div></div>';
		} else {
			echo '<div class="edu-grid">';
			foreach ( $courses as $course ) {
				echo '<div class="edu-card">';
				echo '<h3>' . esc_html( $course->title ) . '</h3>';
				echo '<p>' . esc_html( wp_trim_words( $course->description, 20 ) ) . '</p>';

				$modules = $wpdb->get_results( $wpdb->prepare( "SELECT id, title FROM {$wpdb->prefix}edu_modules WHERE course_id = %d ORDER BY order_index ASC", $course->id ) );

				if ( ! empty( $modules ) ) {
					foreach ( $modules as $module ) {
						echo '<div class="edu-module-summary" style="margin-top:10px;">';
						echo '<strong style="font-size: 0.9em; color: #666;">' . esc_html( $module->title ) . '</strong>';
						$lessons = $wpdb->get_results( $wpdb->prepare( "SELECT id, title FROM {$wpdb->prefix}edu_lessons WHERE module_id = %d ORDER BY order_index ASC", $module->id ) );
						if ( ! empty( $lessons ) ) {
							echo '<ul style="margin-left:15px; margin-bottom:10px;">';
							foreach ( $lessons as $lesson ) {
								echo '<li><a href="' . add_query_arg( array( 'edu_lesson' => $lesson->id ), get_permalink() ) . '" style="font-size: 0.9em;">' . esc_html( $lesson->title ) . '</a></li>';
							}
							echo '</ul>';
						}
						echo '</div>';
					}
				} else {
					// Fallback to flat list of lessons if no modules
					$lessons = $wpdb->get_results( $wpdb->prepare( "SELECT id, title FROM {$wpdb->prefix}edu_lessons WHERE course_id = %d ORDER BY order_index ASC", $course->id ) );
					if ( ! empty( $lessons ) ) {
						echo '<ul style="margin-top:15px; border-top:1px solid #eee; padding-top:10px;">';
						foreach ( $lessons as $lesson ) {
							echo '<li><a href="' . add_query_arg( array( 'edu_lesson' => $lesson->id ), get_permalink() ) . '">' . esc_html( $lesson->title ) . '</a></li>';
						}
						echo '</ul>';
					}
				}

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
			$duration = get_option( 'edu_affiliate_cookie_duration', 30 );
			setcookie( 'edu_affiliate', sanitize_text_field( $_GET['ref'] ), time() + ( $duration * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN );
		}
	}

	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'edu' ) !== false || strpos( $hook, 'edupreneur' ) !== false ) {
			wp_enqueue_style( 'edu-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', array(), EDUPRENEUR_PRO_VERSION );

			wp_localize_script( 'jquery', 'eduApi', array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			) );

			if ( strpos( $hook, 'page_edu-courses' ) !== false ) {
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'edu-course-builder', plugin_dir_url( __FILE__ ) . 'assets/js/course-builder.js', array( 'jquery', 'jquery-ui-sortable' ), EDUPRENEUR_PRO_VERSION, true );
			}
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
