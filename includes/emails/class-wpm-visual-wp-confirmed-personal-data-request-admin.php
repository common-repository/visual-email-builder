<?php //phpcs:ignore
/**
 * Modify mail goes to admin after user confirms the export request.
 */
class Wpm_Visual_Wp_Confirmed_Personal_Data_Request_Admin {
	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_filter( 'user_request_confirmed_email_subject', array( $this, 'mail_subject' ), 9999, 3 );
		add_filter( 'user_request_confirmed_email_content', array( $this, 'mail_message' ), 9999, 2 );
		add_filter( 'user_request_confirmed_email_headers', array( $this, 'mail_header' ), 9999, 5 );
	}

	/**
	 * Filters the subject of the user request confirmation email.
	 *
	 * @param string $subject The email subject.
	 * @param string $sitename The name of the site.
	 * @param array  $email_data Data relating to the account action email.
	 * @return string
	 */
	public function mail_subject( $subject, $sitename, $email_data ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array(
			'request_type'              => $email_data['description'],
			'data_privacy_requests_url' => $email_data['manage_url'],
			'user_email'                => $email_data['user_email'],
		);

		$email_data_from_post = Wpm_Notification_Email_Helper::get_email_data( 'wp-confirmed-personal-data-request-admin' );

		$subject = isset( $email_data_from_post['subject'] ) ? $email_data_from_post['subject'] : $subject;

		$subject = $mergtags->decoder( 'wp-confirmed-personal-data-request-admin', $subject, $params );

		return $subject;

	}

	/**
	 * Filters the body of the user request confirmation email.
	 *
	 * @param string $content The email content.
	 * @param array  $email_data Data relating to the account action email.
	 * @return string
	 */
	public function mail_message( $content, $email_data ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array(
			'request_type'              => $email_data['description'],
			'data_privacy_requests_url' => $email_data['manage_url'],
			'user_email'                => $email_data['user_email'],
		);

		$email_data_from_post = Wpm_Notification_Email_Helper::get_email_data( 'wp-confirmed-personal-data-request-admin' );

		$content = isset( $email_data_from_post['message'] ) ? $email_data_from_post['message'] : $content;

		$content = $mergtags->decoder( 'wp-confirmed-personal-data-request-admin', $content, $params );

		return $content;
	}

	/**
	 * Filters the headers of the user request confirmation email.
	 *
	 * @param mixed  $headers  (string|array) The email headers.
	 * @param string $subject The email subject.
	 * @param string $content The email content.
	 * @param int    $request_id The request ID.
	 * @param array  $email_data Data relating to the account action email.
	 * @return mixed
	 */
	public function mail_header( $headers, $subject, $content, $request_id, $email_account_data ) {

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-confirmed-personal-data-request-admin' );

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
}

new Wpm_Visual_Wp_Confirmed_Personal_Data_Request_Admin();
