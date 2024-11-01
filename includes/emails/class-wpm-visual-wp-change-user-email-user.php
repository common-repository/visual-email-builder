<?php // phpcs:ignore
/**
 * On user email change confirmation mail.
 */
class Wpm_Visual_Wp_Change_User_Email_User {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'new_user_email_content', array( $this, 'change_user_email' ), 9999, 2 );

	}
	/**
	 * Modify email content.
	 *
	 * @param string $email_text Email Text.
	 * @param array  $new_user_email New Email.
	 * @return array
	 */
	public function change_user_email( $email_text, $new_user_email ) {
		

		$mergtags   = wpm_notification_merge_tags();
		$params     = array(
			'new_user_data' => $new_user_email,
		);
		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-change-user-email-user' );

		if ( empty( $email_data ) ) {
			return $email_text;
		}
		
		add_filter(
			'wp_mail_content_type',
			function( $content_type ) {
				return 'text/html';
			}
		);

		$email_text = isset( $email_data['message'] ) ? $email_data['message'] : $email_text;

		$email_text = $mergtags->decoder( 'wp-change-user-email-user', $email_text, $params );

		return $email_text;
	}


}
new Wpm_Visual_Wp_Change_User_Email_User();
