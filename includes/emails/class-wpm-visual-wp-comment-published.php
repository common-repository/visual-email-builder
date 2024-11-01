<?php // phpcs:ignore
/**
 * Modify mail to post author after comment is approved
 */
class Wpm_Visual_Wp_Comment_Published {
	/**
	 * Class Constructor
	 */
	public function __construct() {
		add_filter( 'comment_notification_subject', array( $this, 'comment_subject' ), 9999, 2 );
		add_filter( 'comment_notification_text', array( $this, 'comment_message' ), 9999, 2 );
		add_filter( 'comment_notification_headers', array( $this, 'comment_header' ), 9999, 2 );
		// add_action( 'admin_init', array( $this, 'init_function' ) );

	}
	
	function init_function(){

		$hlo = Wpm_Notification_Email_Helper::get_email_data( 'wp-signup-admin', 'iddentifdy' );


	}

	/**
	 * Filters the comment notification email subject.
	 *
	 * @param string $subject The comment notification email subject.
	 * @param int    $comment_id Comment ID.
	 * @return string
	 */
	public function comment_subject( $subject, $comment_id ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array( 'comment_id' => $comment_id );

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-comment-published-post-author' );

		$subject = isset( $email_data['subject'] ) ? $email_data['subject'] : $subject;

		$subject = $mergtags->decoder( 'wp-comment-published-post-author', $subject, $params );

		return $subject;
	}

	/**
	 * Filters the comment notification email headers.
	 *
	 * @param string $message_header Headers for the comment notification email.
	 * @param int    $comment_id Comment ID.
	 * @return string
	 */
	public function comment_header( $message_header, $comment_id ) {

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-comment-published-post-author' );

		if ( empty( $email_data ) ) {
			return $message_header;
		}

		$message_header = 'Content-Type: text/html; charset=UTF-8 ' . "\r\n";

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
	 * Filters the comment notification email text.
	 *
	 * @param string $message The comment notification email text.
	 * @param int    $comment_id Comment ID.
	 * @return string
	 */
	public function comment_message( $message, $comment_id ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array( 'comment_id' => $comment_id );

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-comment-published-post-author' );
		$message    = isset( $email_data['message'] ) ? $email_data['message'] : $message;

		$message = $mergtags->decoder( 'wp-comment-published-post-author', $message, $params );

		return $message;
	}


}

new Wpm_Visual_Wp_Comment_Published();
