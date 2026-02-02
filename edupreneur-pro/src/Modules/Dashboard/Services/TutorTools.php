<?php

namespace EdupreneurPro\Modules\Dashboard\Services;

/**
 * Tutor Tools Class
 */
class TutorTools {

	/**
	 * Generate a coupon code.
	 *
	 * @param string $code
	 * @param float  $discount
	 * @param string $type (percentage|flat)
	 * @return bool
	 */
	public function create_coupon( $code, $discount, $type = 'percentage' ) {
		// Logic to save coupon to options or custom table
		return update_option( 'edu_coupon_' . sanitize_key( $code ), array(
			'discount' => $discount,
			'type'     => $type,
		) );
	}

	/**
	 * Add a note to a student.
	 *
	 * @param int    $student_id
	 * @param string $note
	 * @return bool
	 */
	public function add_student_note( $student_id, $note ) {
		$notes = get_user_meta( $student_id, '_edu_tutor_notes', true ) ?: array();
		$notes[] = array(
			'date' => current_time( 'mysql' ),
			'note' => sanitize_textarea_field( $note ),
		);
		return update_user_meta( $student_id, '_edu_tutor_notes', $notes );
	}
}
