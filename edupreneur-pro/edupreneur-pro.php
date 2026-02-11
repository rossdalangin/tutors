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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'wp_head', array( $this, 'add_pwa_tags' ) );
		add_action( 'init', array( $this, 'track_affiliate_referral' ) );
		add_action( 'init', array( $this, 'handle_lesson_completion' ) );
		add_action( 'init', array( $this, 'handle_course_redirects' ) );
		add_action( 'init', array( $this, 'ensure_admin_capabilities' ) );
		add_shortcode( 'edu_student_dashboard', array( $this, 'render_student_dashboard' ) );
		add_filter( 'the_content', array( $this, 'handle_lesson_display' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	/**
	 * Ensure roles have necessary capabilities.
	 */
	public function ensure_admin_capabilities() {
		if ( current_user_can( 'read' ) ) {
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

	public function handle_course_redirects() {
		global $wpdb;
		if ( isset( $_GET['edu_course_id'] ) && is_user_logged_in() && ! isset( $_GET['edu_lesson'] ) ) {
			$course_id = intval( $_GET['edu_course_id'] );
			$student_id = get_current_user_id();

			// Check if enrolled before redirecting
			$is_enrolled = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}edu_enrollments WHERE student_id = %d AND course_id = %d AND status = 'active'", $student_id, $course_id ) );
			if ( ! $is_enrolled ) {
				return;
			}

			$next_lesson = $wpdb->get_var( $wpdb->prepare( "SELECT l.id FROM {$wpdb->prefix}edu_lessons l LEFT JOIN {$wpdb->prefix}edu_progress p ON l.id = p.lesson_id AND p.student_id = %d WHERE l.course_id = %d AND (p.completed IS NULL OR p.completed = 0) ORDER BY l.order_index ASC LIMIT 1", $student_id, $course_id ) );
			if ( $next_lesson ) {
				wp_safe_redirect( add_query_arg( 'edu_lesson', $next_lesson ) );
				exit;
			}
		}
	}

	public function handle_lesson_display( $content ) {
		global $wpdb;

		if ( isset( $_GET['buy_course'] ) ) {
			$shortcodes = new \EdupreneurPro\Modules\CourseBuilder\Services\ShortcodeService();
			return $shortcodes->render_checkout();
		}

		if ( isset( $_GET['edu_category'] ) ) {
			return $this->render_category_courses( sanitize_text_field( $_GET['edu_category'] ) );
		}

		$lesson_id = 0;

		if ( isset( $_GET['edu_lesson'] ) ) {
			$lesson_id = intval( $_GET['edu_lesson'] );
		}

		if ( ! $lesson_id && isset( $_GET['edu_course_id'] ) ) {
			if ( is_user_logged_in() ) {
				$course_id = intval( $_GET['edu_course_id'] );
				$student_id = get_current_user_id();

				// Check enrollment for logged in users
				$is_enrolled = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}edu_enrollments WHERE student_id = %d AND course_id = %d AND status = 'active'", $student_id, $course_id ) );

				if ( $is_enrolled ) {
					$next_lesson = $wpdb->get_var( $wpdb->prepare( "SELECT l.id FROM {$wpdb->prefix}edu_lessons l LEFT JOIN {$wpdb->prefix}edu_progress p ON l.id = p.lesson_id AND p.student_id = %d WHERE l.course_id = %d AND (p.completed IS NULL OR p.completed = 0) ORDER BY l.order_index ASC LIMIT 1", $student_id, $course_id ) );
					if ( $next_lesson ) {
						$lesson_id = $next_lesson;
					}
				}
			}

			if ( ! $lesson_id ) {
				return $this->render_course_sales_page( intval( $_GET['edu_course_id'] ) );
			}
		}

		if ( ! $lesson_id ) {
			return $content;
		}

		$lesson = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_lessons WHERE id = %d", $lesson_id ) );

		if ( ! $lesson ) {
			if ( is_admin() ) {
				return '<div class="edu-card edu-error"><h3>' . esc_html__( 'Lesson Not Found', 'edupreneur-pro' ) . '</h3><p>' . esc_html__( 'The requested lesson could not be found. Please return to the dashboard and try again.', 'edupreneur-pro' ) . '</p></div>';
			}
			return $content;
		}

		// Authorization check
		if ( ! current_user_can( 'read' ) ) {
			return '<p>' . esc_html__( 'Please log in to access this lesson.', 'edupreneur-pro' ) . '</p>';
		}

		// Check enrollment for students (admins can always see it)
		if ( ! current_user_can( 'manage_edu_lessons' ) ) {
			$student_id = get_current_user_id();
			$is_enrolled = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}edu_enrollments WHERE student_id = %d AND course_id = %d AND status = 'active'", $student_id, $lesson->course_id ) );
			if ( ! $is_enrolled ) {
				return '<div class="edu-card edu-warning"><h3>' . esc_html__( 'Enrollment Required', 'edupreneur-pro' ) . '</h3><p>' . esc_html__( 'You must be enrolled in this course to view this lesson.', 'edupreneur-pro' ) . '</p><a href="' . add_query_arg( 'edu_course_id', $lesson->course_id, get_permalink() ) . '" class="edu-btn">' . esc_html__( 'View Enrollment Options', 'edupreneur-pro' ) . '</a></div>';
			}
		}

		// Progress Service Check (Drip)
		$progress = new \EdupreneurPro\Modules\CourseBuilder\Services\ProgressService();
		if ( ! $progress->can_access_lesson( get_current_user_id(), $lesson_id ) ) {
			return '<div class="edu-card edu-warning"><h3>' . esc_html__( 'Lesson Locked', 'edupreneur-pro' ) . '</h3><p>' . esc_html__( 'This lesson is not yet available based on your enrollment drip schedule.', 'edupreneur-pro' ) . '</p></div>';
		}

		$back_url = ( is_admin() && isset( $_GET['page'] ) ) ? admin_url( 'admin.php?page=' . sanitize_text_field( $_GET['page'] ) ) : get_permalink();

		ob_start();
		echo '<div class="edu-lesson-player">';
		echo '<a href="' . $back_url . '" class="edu-btn edu-btn-small" style="margin-bottom:20px;">' . esc_html__( '← Back to Dashboard', 'edupreneur-pro' ) . '</a>';
		echo '<h1>' . esc_html( $lesson->title ) . '</h1>';

		if ( ! empty( $lesson->video_url ) ) {
			echo '<div class="edu-video-container" style="margin: 20px 0;">';
			echo $this->get_video_embed( $lesson->video_url );
			echo '</div>';
		}

		echo '<div class="edu-lesson-content">' . wpautop( $lesson->content ) . '</div>';

		if ( $lesson->lesson_type === 'quiz' ) {
			$quiz = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_quizzes WHERE lesson_id = %d", $lesson_id ) );
			if ( $quiz && ! empty( $quiz->questions ) ) {
				$questions = json_decode( $quiz->questions, true );
				if ( is_array( $questions ) ) {
					echo '<div class="edu-quiz-player edu-card" style="margin-top:30px; border-left: 5px solid var(--edu-primary);">';
					echo '<h3>' . esc_html( $quiz->title ) . '</h3>';
					echo '<form id="edu-quiz-form">';
					foreach ( $questions as $q_idx => $q ) {
						echo '<div class="edu-quiz-question" style="margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid #eee;">';
						echo '<p><strong>' . ( $q_idx + 1 ) . '. ' . esc_html( $q['q'] ) . '</strong></p>';
						if ( isset( $q['a'] ) && is_array( $q['a'] ) ) {
							foreach ( $q['a'] as $a_idx => $ans ) {
								$correct = (int)$q['c'] === $a_idx ? '1' : '0';
								echo '<label style="display:block; margin: 8px 0; cursor:pointer;"><input type="radio" name="q_' . $q_idx . '" value="' . $a_idx . '" data-correct="' . $correct . '"> ' . esc_html( $ans ) . '</label>';
							}
						}
						echo '</div>';
					}
					echo '<button type="button" id="edu-submit-quiz" class="edu-btn">' . esc_html__( 'Check My Answers', 'edupreneur-pro' ) . '</button>';
					echo '<div id="edu-quiz-results" style="margin-top:20px; display:none;"></div>';
					echo '</form>';
					echo '</div>';

					echo '<script>
					jQuery("#edu-submit-quiz").click(function(){
						let total = jQuery(".edu-quiz-question").length;
						let correct = 0;
						let complete = true;
						jQuery(".edu-quiz-question").each(function(idx){
							let selected = jQuery("input[name=\'q_\'+idx+\'\']:checked");
							if(selected.length === 0) { complete = false; return false; }
							if(selected.data("correct") == "1") { correct++; }
						});
						if(!complete) { alert("Please answer all questions."); return; }
						let pct = Math.round((correct / total) * 100);
						let msg = "You got " + correct + " out of " + total + " correct (" + pct + "%).";
						let resultBox = jQuery("#edu-quiz-results");
						resultBox.html("<div class=\'edu-card\' style=\'background:#f8f9fa; border:2px solid " + (pct >= 70 ? "#28a745" : "#dc3545") + ";\'><h4>" + msg + "</h4>" + (pct >= 70 ? "<p>Great job! You can now mark this lesson as completed.</p>" : "<p>Try again to improve your score.</p>") + "</div>").show();
					});
					</script>';
				}
			}
		}

		$resources = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_resources WHERE lesson_id = %d", $lesson_id ) );
		if ( ! empty( $resources ) ) {
			echo '<div class="edu-card" style="margin-top:30px;"><h3>' . esc_html__( 'Learning Resources', 'edupreneur-pro' ) . '</h3><ul>';
			foreach ( $resources as $res ) {
				echo '<li><a href="' . esc_url( $res->url ) . '" target="_blank">' . esc_html( $res->title ) . '</a></li>';
			}
			echo '</ul></div>';
		}

		// Navigation and Completion
		$next_lesson_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}edu_lessons WHERE course_id = %d AND order_index > %d ORDER BY order_index ASC LIMIT 1", $lesson->course_id, $lesson->order_index ) );

		echo '<div style="margin-top:40px; display:flex; gap:20px; align-items:center;">';
		echo '<form method="post">';
		wp_nonce_field( 'edu_complete_lesson' );
		echo '<input type="hidden" name="lesson_to_complete" value="' . $lesson_id . '">';
		echo '<button type="submit" class="edu-btn" style="background:var(--edu-success);">' . esc_html__( 'Mark as Completed', 'edupreneur-pro' ) . '</button>';
		echo '</form>';

		if ( $next_lesson_id ) {
			echo '<a href="' . add_query_arg( 'edu_lesson', $next_lesson_id, $back_url ) . '" class="edu-btn" style="background:var(--edu-secondary);">' . esc_html__( 'Next Lesson →', 'edupreneur-pro' ) . '</a>';
		}
		echo '</div>';

		echo '</div>';
		return ob_get_clean();
	}

	public function render_course_sales_page( $course_id ) {
		global $wpdb;
		$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_courses WHERE id = %d", $course_id ) );
		if ( ! $course ) return '<p>' . __( 'Course not found.', 'edupreneur-pro' ) . '</p>';

		$instructor = get_userdata( $course->instructor_id );
		$base_url = ( is_admin() && isset( $_GET['page'] ) ) ? admin_url( 'admin.php?page=' . sanitize_text_field( $_GET['page'] ) ) : get_permalink();

		ob_start();
		echo '<div class="edu-sales-page">';
		echo '<h1>' . esc_html( $course->title ) . '</h1>';
		echo '<div class="edu-card" style="margin:20px 0;">';
		echo '<div style="margin-bottom:10px;"><span class="tag">' . esc_html( $course->category ) . '</span></div>';
		echo '<p>' . wp_kses_post( $course->description ) . '</p>';
		echo '<p><strong>' . __( 'Instructor:', 'edupreneur-pro' ) . '</strong> ' . ( $instructor ? $instructor->display_name : 'Expert' ) . '</p>';
		echo '<div style="font-size:1.5em; color:var(--edu-primary); margin:20px 0;">$' . number_format( $course->price, 2 ) . '</div>';
		echo '<a href="' . add_query_arg( 'buy_course', $course->id, $base_url ) . '" class="edu-btn edu-btn-block">' . __( 'Enroll Now', 'edupreneur-pro' ) . '</a>';
		echo '</div>';

		echo '<h2>' . __( 'Course Curriculum', 'edupreneur-pro' ) . '</h2>';

		// Orphan lessons
		$orphan_lessons = $wpdb->get_results( $wpdb->prepare( "SELECT title FROM {$wpdb->prefix}edu_lessons WHERE course_id = %d AND (module_id = 0 OR module_id IS NULL) ORDER BY order_index ASC", $course_id ) );
		if ( ! empty( $orphan_lessons ) ) {
			echo '<div class="edu-card" style="margin-bottom:10px;"><h3>' . __( 'Introductory Lessons', 'edupreneur-pro' ) . '</h3>';
			echo '<ul>';
			foreach ( $orphan_lessons as $lesson ) {
				echo '<li>' . esc_html( $lesson->title ) . '</li>';
			}
			echo '</ul></div>';
		}

		$modules = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_modules WHERE course_id = %d ORDER BY order_index ASC", $course_id ) );
		foreach ( $modules as $module ) {
			echo '<div class="edu-card" style="margin-bottom:10px;"><h3>' . esc_html( $module->title ) . '</h3>';
			$lessons = $wpdb->get_results( $wpdb->prepare( "SELECT title FROM {$wpdb->prefix}edu_lessons WHERE module_id = %d ORDER BY order_index ASC", $module->id ) );
			echo '<ul>';
			foreach ( $lessons as $lesson ) {
				echo '<li>' . esc_html( $lesson->title ) . '</li>';
			}
			echo '</ul></div>';
		}
		echo '</div>';
		return ob_get_clean();
	}

	public function render_category_courses( $category ) {
		global $wpdb;
		$courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edu_courses WHERE (category = %s OR category_id = (SELECT id FROM {$wpdb->prefix}edu_categories WHERE slug = %s)) AND status = 'publish'", $category, $category ) );
		$base_url = ( is_admin() && isset( $_GET['page'] ) ) ? admin_url( 'admin.php?page=' . sanitize_text_field( $_GET['page'] ) ) : get_permalink();

		ob_start();
		echo '<h1>' . sprintf( __( 'Courses in %s', 'edupreneur-pro' ), esc_html( $category ) ) . '</h1>';
		echo '<div class="edu-grid">';
		foreach ( $courses as $course ) {
			echo '<div class="edu-card">';
			echo '<h3>' . esc_html( $course->title ) . '</h3>';
			echo '<p>' . esc_html( wp_trim_words( $course->description, 15 ) ) . '</p>';
			echo '<a href="' . add_query_arg( 'edu_course_id', $course->id, $base_url ) . '" class="edu-btn">' . __( 'View Details', 'edupreneur-pro' ) . '</a>';
			echo '</div>';
		}
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

		$base_url = ( is_admin() && isset( $_GET['page'] ) ) ? admin_url( 'admin.php?page=' . sanitize_text_field( $_GET['page'] ) ) : get_permalink();

		ob_start();
		echo '<div class="edu-student-dashboard">';
		echo '<h2>' . esc_html__( 'My Learning Journey', 'edupreneur-pro' ) . '</h2>';
		echo '<p>' . esc_html__( 'Welcome back, learner! Continue where you left off and achieve your educational goals.', 'edupreneur-pro' ) . '</p>';

		if ( empty( $courses ) ) {
			echo '<div class="edu-grid"><div class="edu-card" style="grid-column: 1/-1;"><h3>' . esc_html__( 'No Courses Found', 'edupreneur-pro' ) . '</h3><p>' . esc_html__( 'You haven\'t enrolled in any courses yet.', 'edupreneur-pro' ) . '</p>';
			echo '<a href="' . $base_url . '" class="edu-btn">' . esc_html__( 'Browse Course Catalog', 'edupreneur-pro' ) . '</a></div></div>';
		} else {
			echo '<div class="edu-grid">';
			foreach ( $courses as $course ) {
				$total_lessons = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}edu_lessons WHERE course_id = %d", $course->id ) );
				$completed_lessons = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}edu_progress WHERE student_id = %d AND course_id = %d AND completed = 1", $student_id, $course->id ) );
				$progress_pct = $total_lessons > 0 ? round( ( $completed_lessons / $total_lessons ) * 100 ) : 0;

				echo '<div class="edu-card">';
				echo '<div style="display:flex; justify-content:space-between; align-items:flex-start;">';
				echo '<div style="flex-grow:1;"><span class="tag" style="margin-bottom:5px; display:inline-block;">' . esc_html( $course->category ) . '</span>';
				echo '<h3 style="margin-top:0;">' . esc_html( $course->title ) . '</h3></div>';
				echo '<span class="edu-badge">' . $progress_pct . '%</span>';
				echo '</div>';

				echo '<div style="background:#eee; height:8px; border-radius:4px; margin:10px 0; overflow:hidden;">';
				echo '<div style="background:var(--edu-primary); height:100%; width:' . $progress_pct . '%;"></div>';
				echo '</div>';

				echo '<p class="edu-caption">' . sprintf( __( '%d of %d lessons completed', 'edupreneur-pro' ), $completed_lessons, $total_lessons ) . '</p>';

				// Display lessons without a module first
				$orphan_lessons = $wpdb->get_results( $wpdb->prepare( "SELECT id, title FROM {$wpdb->prefix}edu_lessons WHERE course_id = %d AND (module_id = 0 OR module_id IS NULL) ORDER BY order_index ASC", $course->id ) );
				if ( ! empty( $orphan_lessons ) ) {
					echo '<div class="edu-module-summary" style="margin-top:15px; border-top:1px solid #f0f0f0; padding-top:10px;">';
					echo '<strong style="font-size: 0.85em; color: #888; text-transform:uppercase;">' . esc_html__( 'Introduction', 'edupreneur-pro' ) . '</strong>';
					echo '<ul style="margin: 5px 0 0 0; padding:0;">';
					foreach ( $orphan_lessons as $lesson ) {
						$is_done = $wpdb->get_var( $wpdb->prepare( "SELECT completed FROM {$wpdb->prefix}edu_progress WHERE student_id = %d AND lesson_id = %d", $student_id, $lesson->id ) );
						$done_class = $is_done ? 'is-completed' : '';
						$icon = $is_done ? '✅' : '📄';
						echo '<li style="list-style:none; margin-bottom:5px;"><a href="' . add_query_arg( array( 'edu_lesson' => $lesson->id ), $base_url ) . '" class="edu-lesson-link ' . $done_class . '"><span class="edu-check-icon">' . $icon . '</span> ' . esc_html( $lesson->title ) . '</a></li>';
					}
					echo '</ul></div>';
				}

				$modules = $wpdb->get_results( $wpdb->prepare( "SELECT id, title FROM {$wpdb->prefix}edu_modules WHERE course_id = %d ORDER BY order_index ASC", $course->id ) );

				if ( ! empty( $modules ) ) {
					foreach ( $modules as $module ) {
						echo '<div class="edu-module-summary" style="margin-top:15px; border-top:1px solid #f0f0f0; padding-top:10px;">';
						echo '<strong style="font-size: 0.85em; color: #888; text-transform:uppercase;">' . esc_html( $module->title ) . '</strong>';
						$lessons = $wpdb->get_results( $wpdb->prepare( "SELECT id, title FROM {$wpdb->prefix}edu_lessons WHERE module_id = %d ORDER BY order_index ASC", $module->id ) );
						if ( ! empty( $lessons ) ) {
							echo '<ul style="margin: 5px 0 0 0; padding:0;">';
							foreach ( $lessons as $lesson ) {
								$is_done = $wpdb->get_var( $wpdb->prepare( "SELECT completed FROM {$wpdb->prefix}edu_progress WHERE student_id = %d AND lesson_id = %d", $student_id, $lesson->id ) );
								$done_class = $is_done ? 'is-completed' : '';
								$icon = $is_done ? '✅' : '📄';
								echo '<li style="list-style:none; margin-bottom:5px;"><a href="' . add_query_arg( array( 'edu_lesson' => $lesson->id ), $base_url ) . '" class="edu-lesson-link ' . $done_class . '"><span class="edu-check-icon">' . $icon . '</span> ' . esc_html( $lesson->title ) . '</a></li>';
							}
							echo '</ul>';
						}
						echo '</div>';
					}
				}

				echo '<a href="' . add_query_arg( array( 'edu_course_id' => $course->id ), $base_url ) . '" class="edu-btn edu-btn-block" style="margin-top:20px;">' . esc_html__( 'Continue Learning', 'edupreneur-pro' ) . '</a>';
				echo '</div>';
			}
			echo '</div>';
		}

		$is_affiliate = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}edu_affiliates WHERE user_id = %d", $student_id ) );
		if ( ! $is_affiliate ) {
			echo '<div class="edu-card" style="margin-top:40px; background:linear-gradient(to right, #6a11cb 0%, #2575fc 100%); color:#fff; border:none;">';
			echo '<h3 style="color:#fff;">' . esc_html__( 'Earn While You Learn!', 'edupreneur-pro' ) . '</h3>';
			echo '<p>' . esc_html__( 'Join our affiliate program and earn commissions for every student you refer to our platform.', 'edupreneur-pro' ) . '</p>';
			echo '<button id="student-join-affiliate" class="edu-btn" style="background:#fff; color:#2575fc; font-weight:700;">' . esc_html__( 'Become an Affiliate', 'edupreneur-pro' ) . '</button>';
			echo '<script>jQuery("#student-join-affiliate").click(function(){ jQuery.post(eduApi.root + "edupreneur/v1/affiliates/register", { _wpnonce: eduApi.nonce }, function(){ location.reload(); }); });</script>';
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
	 * Enqueue frontend assets.
	 */
	public function enqueue_frontend_assets() {
		wp_enqueue_style( 'edu-frontend-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', array(), EDUPRENEUR_PRO_VERSION );
	}

	/**
	 * Add PWA meta tags and manifest.
	 */
	public function add_pwa_tags() {
		echo '<link rel="manifest" href="' . plugin_dir_url( __FILE__ ) . 'manifest.json">' . PHP_EOL;
		echo '<meta name="theme-color" content="#4a90e2">' . PHP_EOL;
		echo '<meta name="mobile-web-app-capable" content="yes">' . PHP_EOL;
		echo '<meta name="apple-mobile-web-app-capable" content="yes">' . PHP_EOL;
		echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . PHP_EOL;
		echo '<meta name="apple-mobile-web-app-title" content="EdupreneurPro">' . PHP_EOL;
	}

	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'edu' ) !== false || strpos( $hook, 'edupreneur' ) !== false ) {
			wp_enqueue_style( 'edu-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', array(), EDUPRENEUR_PRO_VERSION );
			wp_enqueue_script( 'edu-admin-helpers', plugin_dir_url( __FILE__ ) . 'assets/js/admin-helpers.js', array( 'jquery' ), EDUPRENEUR_PRO_VERSION, true );

			$courses = array();
			if ( strpos( $hook, 'page_edu-courses' ) !== false ) {
				$repo = new \EdupreneurPro\Modules\CourseBuilder\Repositories\CourseRepository();
				$courses = $repo->all();
			}

			wp_localize_script( 'jquery', 'eduApi', array(
				'root'    => esc_url_raw( rest_url() ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'siteUrl' => esc_url( home_url( '/' ) ),
				'courses' => $courses
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

	/**
	 * Get video embed code from URL.
	 */
	private function get_video_embed( $url ) {
		$embed_url = '';
		if ( strpos( $url, 'youtube.com' ) !== false || strpos( $url, 'youtu.be' ) !== false ) {
			if ( preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $match ) ) {
				$video_id = $match[1];
				$embed_url = "https://www.youtube.com/embed/{$video_id}";
			}
		} elseif ( strpos( $url, 'vimeo.com' ) !== false ) {
			if ( preg_match( '/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/i', $url, $match ) ) {
				$video_id = $match[1];
				$embed_url = "https://player.vimeo.com/video/{$video_id}";
			}
		}

		if ( $embed_url ) {
			return '<iframe width="100%" height="auto" src="' . esc_url( $embed_url ) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="aspect-ratio: 16/9; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);"></iframe>';
		}

		return '<div class="edu-video-placeholder" style="background:#000; color:#fff; aspect-ratio:16/9; display:flex; align-items:center; justify-content:center; border-radius:12px;">' . esc_html__( 'Video Link:', 'edupreneur-pro' ) . ' ' . esc_url( $url ) . '</div>';
	}
}

function edupreneur_pro_init() {
	return EdupreneurPro::instance();
}
edupreneur_pro_init();
