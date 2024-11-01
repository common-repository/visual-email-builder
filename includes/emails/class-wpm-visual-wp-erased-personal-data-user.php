<?php
/**
 * Modify mail goes to user on when data is erased.
 */
class Wpm_Visual_Wp_Erased_Personal_Data_User {

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		global $wp_version;

		if ( version_compare( $wp_version, '5.8.0', '>=' ) ) {

			add_filter( 'user_erasure_fulfillment_email_subject', array( $this, 'mail_subject' ), 9999, 3 );
			add_filter( 'user_erasure_fulfillment_email_content', array( $this, 'mail_message' ), 9999, 2 );
			add_filter( 'user_erasure_fulfillment_email_headers', array( $this, 'mail_header' ), 9999, 5 );

		} else {

			add_filter( 'user_erasure_complete_email_subject ', array( $this, 'mail_subject' ), 9999, 3 );
			add_filter( 'user_confirmed_action_email_content ', array( $this, 'mail_message' ), 9999, 2 );
			add_filter( 'user_erasure_complete_email_headers ', array( $this, 'mail_header' ), 9999, 5 );
		}

	}

	/**
	 * Filters the subject of the email sent when an erasure request is completed.
	 *
	 * @param string $subject The email subject.
	 * @param string $sitename The name of the site.
	 * @param array  $email_data Data relating to the account action email.
	 * @return string
	 */
	public function mail_subject( $subject, $sitename, $email_data ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array( 'sitename' => $sitename );

		$email_data_from_post = Wpm_Notification_Email_Helper::get_email_data( 'wp-erased-personal-data-user' );

		$subject = isset( $email_data_from_post['subject'] ) ? $email_data_from_post['subject'] : $subject;

		$subject = $mergtags->decoder( 'wp-erased-personal-data-user', $subject, $params );


		return $subject;
	}

	/**
	 * Filters the body of the data erasure fulfillment notification.
	 *
	 * @param string $content The email content.
	 * @param array  $email_data Data relating to the account action email.
	 * @return string
	 */
	public function mail_message( $content, $email_data ) {
		$mergtags = wpm_notification_merge_tags();
		$params   = array( 'sitename' => $email_data['sitename'] );

		$email_data_from_post = Wpm_Notification_Email_Helper::get_email_data( 'wp-erased-personal-data-user' );

		$content = isset( $email_data_from_post['message'] ) ? $email_data_from_post['message'] : $content;

		$content = $mergtags->decoder( 'wp-erased-personal-data-user', $content, $params );

		return $content;
	}

	/**
	 * Filters the headers of the data erasure fulfillment notification.
	 *
	 * @param mixed  $headers (string|array) The email headers.
	 * @param string $subject The email subject.
	 * @param string $content The email content.
	 * @param int    $request_id The request ID.
	 * @param array  $email_data Data relating to the account action email.
	 * @return mixed (string|array)
	 */
	public function mail_header( $headers, $subject, $content, $request_id, $email_data ) {

		$email_data_from_post = Wpm_Notification_Email_Helper::get_email_data( 'wp-erased-personal-data-user' );

		if ( empty( $email_data ) ) {
			return $headers;
		}
		
		$headers = 'Content-Type: text/html; charset=UTF-8 ' . "\r\n";

		foreach ( $email_data_from_post as $email_key => $email_value ) {

			if ( is_array( $email_value ) ) {
				foreach ( $email_value as $value ) {
					$headers .= $value;
				}
			}
		}

		return $headers;
	}

}

new Wpm_Visual_Wp_Erased_Personal_Data_User();
