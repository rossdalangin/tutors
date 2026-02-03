<?php
namespace EdupreneurPro\Modules\Community\Services;

class Engagement {
	public function schedule_qna( $course_id, $title, $scheduled_at ) {
		return true; // Mock implementation
	}

	public function generate_calendar_link( $title, $datetime ) {
		$start = date( 'Ymd\THis', strtotime( $datetime ) );
		$end = date( 'Ymd\THis', strtotime( $datetime . ' +1 hour' ) );
		return "https://www.google.com/calendar/render?action=TEMPLATE&text=" . urlencode( $title ) . "&dates={$start}/{$end}";
	}
}
