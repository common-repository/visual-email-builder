<?php // phpcs:ignore
/**
 * Class to Modify the Signup Admin Email.
 */
class Wpm_Visual_Wp_Signup_Admin {

	/**
	 * Class Constructer.
	 */
	public function __construct() {
		add_filter( 'wp_new_user_notification_email_admin', array( $this, 'modify_email' ), 9999, 3 );
	}

	/**
	 * Modify Email.
	 *
	 * @param array  $email Email Data.
	 * @param object $user User Data.
	 * @param string $blog Site Title.
	 * @return array
	 */
	public function modify_email( $email, $user, $blog ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array( 'registered_user' => $user );

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-signup-admin' );

		if ( empty( $email_data ) ) {
			return $email;
		}


		$email['headers'] = 'Content-Type: text/html; charset=UTF-8 ' . "\r\n";

		foreach ( $email_data as $email_key => $email_value ) {
			if ( empty( $email_value ) ) {
				continue;
			}

			if ( ! is_array( $email_value ) ) {

				$content = $mergtags->decoder( 'wp-signup-admin', $email_value, $params );

				$email[ $email_key ] = $content;

			} else { // For adding Header Values.
				$email[ $email_key ] = isset( $email[ $email_key ] ) ? $email[ $email_key ] : array();

				foreach ( $email_value as $value ) {
					$email[ $email_key ] .= $value;
				}
			}
		}

		return $email;
	}
}

new Wpm_Visual_Wp_Signup_Admin();
