<?php // phpcs:ignore
/** 
 * Modify mail to user on lost possword.
 */
class Wpm_Visual_Wp_Lost_Password_User {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'retrieve_password_message', array( $this, 'modify_message' ), 9999, 4 );
		add_filter( 'retrieve_password_title', array( $this, 'modify_title' ), 9999, 3 );
		// add_action( 'init', array( $this ,'init' ) );
	}
	/**
	 * Modify email title.
	 *
	 * @param string $title title of Mail.
	 * @param string $user_login username for the user.
	 * @param object $user_data user info.
	 * @return string
	 */
	public function modify_title( $title, $user_login, $user_data ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array( 'user' => $user_data );

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-lost-password-user' );
		$title      = isset( $email_data['subject'] ) ? $email_data['subject'] : $title;

		$title = $mergtags->decoder( 'wp-lost-password-user', $title, $params );

		return $title;
	}

	/**
	 * Modify Email Data
	 *
	 * @param string $message Message of email.
	 * @param string $key key generated for resetting the password.
	 * @param string $user_login username for the user.
	 * @param object $user_data user info.
	 * @return string
	 */
	public function modify_message( $message, $key, $user_login, $user_data ) {
	
		$mergtags = wpm_notification_merge_tags();
		$params   = array(
			'user' => $user_data,
			'key'  => $key,
		);

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-lost-password-user' );

		if ( empty( $email_data ) ) {
			return $message;
		}

		add_filter( 'wp_mail_content_type', function( $content_type ) {
			return 'text/html';
		} );

		$message    = isset( $email_data['message'] ) ? $email_data['message'] : $message;
		$message    = $mergtags->decoder( 'wp-lost-password-user', $message, $params );

		return $message;
	}
}

new Wpm_Visual_Wp_Lost_Password_User();
