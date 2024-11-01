<?php

/**
 * Modify personal data download mail goes to user.
 */
class Wpm_Visual_Wp_Download_Personal_Data_User {

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_filter( 'wp_privacy_personal_data_email_subject', array( $this, 'mail_subject' ), 9999, 3 );
		add_filter( 'wp_privacy_personal_data_email_content', array( $this, 'mail_message' ), 9999, 3 );
		add_filter( 'wp_privacy_personal_data_email_headers', array( $this, 'mail_header' ), 9999, 5 );
	}

	/**
	 * Filters the subject of the email sent when an export request is completed.
	 *
	 * @param string $subject The email subject.
	 * @param string $sitename  The name of the site.
	 * @param array  $email_data Data relating to the account action email.
	 * @return string
	 */
	public function mail_subject( $subject, $sitename, $email_data ) {

		$email_data_from_post = Wpm_Notification_Email_Helper::get_email_data( 'wp-download-personal-data-user' );

		$subject = isset( $email_data_from_post['subject'] ) ? $email_data_from_post['subject'] : $subject;

		return $subject;

	}

	/**
	 * Filters the text of the email sent with a personal data export file.
	 *
	 * @param string $content Text in the email.
	 * @param int    $request_id The request ID for this personal data export.
	 * @param array  $email_data Data relating to the account action email.
	 * @return string
	 */
	public function mail_message( $content, $request_id, $email_data ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array(
			'expiration_date'              => $email_data['expiration_date'],
			'export_file_url' => $email_data['export_file_url'],
		);

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-download-personal-data-user' );

		$content = isset( $email_data['message'] ) ? $email_data['message'] : $content;

		$content = $mergtags->decoder( 'wp-download-personal-data-user', $content, $params );

		return $content;
	}

	/**
	 * Filters the headers of the email sent with a personal data export file.
	 *
	 * @param mixed  $headers (string|array) The email headers.
	 * @param string $subject The email subject.
	 * @param string $content The email content.
	 * @param int    $request_id  The request ID.
	 * @param array  $email_data Data relating to the account action email.
	 * @return mixed (string|array)
	 */
	public function mail_header( $headers, $subject, $content, $request_id, $email_account_data ) {

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-download-personal-data-user' );

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


new Wpm_Visual_Wp_Download_Personal_Data_User();
