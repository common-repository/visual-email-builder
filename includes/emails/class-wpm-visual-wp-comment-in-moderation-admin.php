<?php // phpcs:ignore
class Wpm_Visual_Wp_Comment_In_Moderation {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_filter( 'comment_moderation_subject', array( $this, 'comment_subject' ), 9999, 2 );
		add_filter( 'comment_moderation_text', array( $this, 'comment_message' ), 9999, 2 );
		add_filter( 'comment_moderation_headers', array( $this, 'comment_header' ), 9999, 2 );
	}
	/**
	 * Modify emal header data.
	 *
	 * @param string $message_header Headers for the comment moderation email.
	 * @param int    $comment_id Comment ID.
	 * @return string
	 */
	public function comment_header( $message_header, $comment_id ) {

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-comment-in-moderation-admin' );

		if ( empty( $email_data ) ) {
			return $message_header;
		}

		$message_header ='Content-Type: text/html; charset=UTF-8 ' . "\r\n";

		foreach ( $email_data as $email_key => $email_value ) {

			if ( is_array( $email_value ) ) {
				foreach ( $email_value as $value ) {
					$message_header .= $value;
				}
			}
		}

		return $message_header;
	}
	/**
	 * Modify comment message data.
	 *
	 * @param string $message Message of the comment moderation email.
	 * @param int    $comment_id Comment ID.
	 * @return string
	 */
	public function comment_message( $message, $comment_id ) {


		$mergtags = wpm_notification_merge_tags();
		$params   = array( 'comment_id' => $comment_id );

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-comment-in-moderation-admin' );

		$message = isset( $email_data['message'] ) ? $email_data['message'] : $message;

		$message = $mergtags->decoder( 'wp-comment-in-moderation-admin', $message, $params );

		return $message;
	}

	/**
	 * Filters the comment moderation email Subject.
	 *
	 * @param string $subject Subject of the comment moderation email.
	 * @param int    $comment_id Comment ID.
	 * @return string
	 */
	public function comment_subject( $subject, $comment_id ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array( 'comment_id' => $comment_id );

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-comment-in-moderation-admin' );

		$subject = isset( $email_data['subject'] ) ? $email_data['subject'] : $subject;
		$subject = $mergtags->decoder( 'wp-comment-in-moderation-admin', $subject, $params );

		return $subject;
	}

}

new Wpm_Visual_Wp_Comment_In_Moderation();
