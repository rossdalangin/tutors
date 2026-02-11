<?php
namespace EdupreneurPro\Modules\CourseBuilder\Services;

class CertificateService {
	public function generate_certificate( $user_id, $course_id ) {
		global $wpdb;
		$user = get_userdata( $user_id );
		$course = $wpdb->get_row( $wpdb->prepare( "SELECT title FROM {$wpdb->prefix}edu_courses WHERE id = %d", $course_id ) );
		$course_title = $course ? $course->title : "Unknown Course";
		$issued_at = date( 'F j, Y', current_time( 'timestamp' ) );
		$verify_token = strtoupper( wp_generate_password( 12, false ) );

		ob_start();
		?>
		<div class="edu-certificate-container" style="padding: 50px; border: 15px solid var(--edu-primary, #4a90e2); text-align: center; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 800px; margin: 0 auto; background: #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1); position: relative;">
			<div style="border: 2px solid #ddd; padding: 40px;">
				<h1 style="font-size: 50px; margin-bottom: 10px; color: #333;">Certificate of Completion</h1>
				<p style="font-size: 18px; color: #666; font-style: italic;">This is to certify that</p>
				<h2 style="font-size: 36px; border-bottom: 2px solid #333; display: inline-block; padding: 0 40px 5px 40px; margin: 20px 0;"><?php echo esc_html( $user->display_name ); ?></h2>
				<p style="font-size: 18px; color: #666;">has successfully completed the course</p>
				<h3 style="font-size: 28px; color: var(--edu-primary, #4a90e2); margin: 20px 0;"><?php echo esc_html( $course_title ); ?></h3>
				<p style="font-size: 16px; color: #888; margin-top: 40px;">Issued on <?php echo $issued_at; ?></p>
				<div style="margin-top: 50px; display: flex; justify-content: space-between; align-items: flex-end;">
					<div style="text-align: left;">
						<p style="font-size: 14px; color: #999; margin: 0;">Verification ID: <?php echo $verify_token; ?></p>
					</div>
					<div style="text-align: right;">
						<p style="font-weight: bold; margin: 0;">EdupreneurPro Academy</p>
						<p style="font-size: 12px; color: #888; margin: 0;">Official Learning Platform</p>
					</div>
				</div>
			</div>
			<div style="margin-top: 30px;" class="no-print">
				<button onclick="window.print()" class="edu-btn">Print / Save as PDF</button>
			</div>
		</div>
		<style>
			@media print {
				.no-print, .edu-header, .edu-sidebar, .admin-bar, #adminmenumain { display: none !important; }
				body { background: none; margin: 0; padding: 0; }
				.edu-certificate-container { border: none; box-shadow: none; margin: 0; width: 100%; max-width: 100%; }
			}
		</style>
		<?php
		return ob_get_clean();
	}
}
