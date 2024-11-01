<?php
/**
 * Modify Export Personal Data email goes to user.
 */
class Wpm_Visual_Wp_Request_Personal_Data_User {

	/**
	 * Class Constructor
	 */
	public function __construct() {
		add_filter( 'user_request_action_email_subject', array( $this, 'mail_subject' ), 9999, 3 );

		add_filter( 'user_request_action_email_content', array( $this, 'mail_message' ), 9999, 2 );
		add_filter( 'user_request_action_email_headers', array( $this, 'mail_header' ), 9999, 5 );
	}

	/**
	 * Filters the headers of the email sent when an account action is attempted.
	 *
	 * @param mixed  $headers The email headers.
	 * @param string $subject The email subject.
	 * @param string $content The email content.
	 * @param int    $request_id The request ID.
	 * @param array  $email_data Data relating to the account action email.
	 * @return mixed
	 */
	public function mail_header( $headers, $subject, $content, $request_id, $email_account_data ) {

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-request-personal-data-user' );

		if ( empty( $email_data ) ) {
			return $headers;
		}

		$headers = 'Content-Type: text/html; charset=UTF-8 ' . "\r\n";

		foreach ( $email_data as $email_key => $email_value ) {

			if ( is_array( $email_value ) ) {
				foreach ( $email_value as $value ) {
					$headers .= $value;
				}
			}
		}

		return $headers;
	}

	/**
	 * Modify mail subject
	 *
	 * @param string $subject The email subject.
	 * @param string $sitename The name of the site.
	 * @param array  $email_data Data relating to the account action email.
	 * @return string
	 */
	public function mail_subject( $subject, $sitename, $email_data ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array(
			'request_type'     => $email_data['description'],
			'confirm_url' => $email_data['confirm_url'],
		);

		$email_data_from_post = Wpm_Notification_Email_Helper::get_email_data( 'wp-request-personal-data-user' );

		$subject = isset( $email_data_from_post['subject'] ) ? $email_data_from_post['subject'] : $subject;

		$subject = $mergtags->decoder( 'wp-request-personal-data-user', $subject, $params );


		return $subject;
	}

	/**
	 * Filters the text of the email sent when an account action is attempted.
	 *
	 * @param string $content Text in the email.
	 * @param array  $email_data Data relating to the account action email.
	 * @return string
	 */
	public function mail_message( $content, $email_data ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array(
			'request_type'     => $email_data['description'],
			'confirm_url' => $email_data['confirm_url'],
		);


		$email_data_from_post = Wpm_Notification_Email_Helper::get_email_data( 'wp-request-personal-data-user' );

		$content = isset( $email_data_from_post['message'] ) ? $email_data_from_post['message'] : $content;

		$content = $mergtags->decoder( 'wp-request-personal-data-user', $content, $params );

		return $content;
	}


}

new Wpm_Visual_Wp_Request_Personal_Data_User();
