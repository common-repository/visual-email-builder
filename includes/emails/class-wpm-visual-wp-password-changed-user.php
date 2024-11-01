<?php 
// phpcs:ignore
/**
 * Update email data on password change.
 */
class Wpm_Visual_Wp_Password_Changed_User {

	/**
	 * Function Constructor.
	 */
	public function __construct() {
		add_filter( 'password_change_email', array( $this, 'modify_email' ), 9999, 3 );
	}
	/**
	 * Modify Email on Password Change.
	 *
	 * @param array  $email Email data.
	 * @param array $user User Data.
	 * @param array $userdata Updated User Data.
	 * @return array
	 */
	public function modify_email( $email, $orignal_user_data, $updated_user_data ) {
		$user_id = $orignal_user_data['ID'];
		$user    = get_userdata( $user_id );

		$mergtags = wpm_notification_merge_tags();
		$params   = array( 'user' => $user );

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-password-changed-user' );

		if ( empty( $email_data ) ) {
			return $email;
		}

		$email['headers'] = 'Content-Type: text/html; charset=UTF-8 ' . "\r\n";

		foreach ( $email_data as $email_key => $email_value ) {
			if ( empty( $email_value ) ) {
				continue;
			}

			if ( ! is_array( $email_value ) ) {

				$content             = $mergtags->decoder( 'wp-password-changed-user', $email_value, $params );
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

new Wpm_Visual_Wp_Password_Changed_User();
