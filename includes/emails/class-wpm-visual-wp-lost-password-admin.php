<?php // phpcs:ignore
/**
 * Modify lost password - admin email.
 */
class Wpm_Visual_Wp_Lost_Password_Admin {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_filter( 'wp_password_change_notification_email', array( $this, 'modify_email' ), 9999, 3 );
	}
	/**
	 * Modify Email Content.
	 *
	 * @param array  $email Email Data.
	 * @param object $user User Data.
	 * @param string $blog Site Title.
	 * @return array
	 */
	public function modify_email( $email, $user, $blog ) {
		$mergtags = wpm_notification_merge_tags();
		$params   = array( 'user' => $user );

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-lost-password-admin' );

		if ( empty( $email_data ) ) {
			return $email;
		}

		$email['headers'] = 'Content-Type: text/html; charset=UTF-8 ' . "\r\n";

		foreach ( $email_data as $email_key => $email_value ) {
			if ( empty( $email_value ) ) {
				continue;
			}

			if ( ! is_array( $email_value ) ) {
				$content = $mergtags->decoder( 'wp-lost-password-admin', $email_value, $params );

				$email[ $email_key ] = $content;
			} else {
				$email[ $email_key ] = isset( $email[ $email_key ] ) ? $email[ $email_key ] : array();

				foreach ( $email_value as $value ) {
					$email[ $email_key ] .= $value;
				}
			}
		}

		return $email;
	}
}

new Wpm_Visual_Wp_Lost_Password_Admin();
