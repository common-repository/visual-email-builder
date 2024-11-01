<?php // phpcs:ignore

/**
 * Update Email data when user's email is changed.
 */
class Wpm_Visual_Wp_Changed_User_Email_User {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'email_change_email', array( $this, 'modify_email' ), 9999, 3 );
	}

	/**
	 * Modify email data.
	 *
	 * @param array $email_info Email Data.
	 * @param array $user Orignal User Array.
	 * @param array $user_data Updated User Array.
	 * @return array
	 */
	public function modify_email( $email_info, $user, $user_data ) {

		$mergtags   = wpm_notification_merge_tags();
		$params     = array(
			'prev_user_data' => $user,
			'new_user_data'  => $user_data,
		);
		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-changed-user-email-user' );

		if ( empty( $email_data ) ) {
			return $email_info;
		}

		$email_info['headers'] = 'Content-Type: text/html; charset=UTF-8 ' . "\r\n";

		foreach ( $email_data as $email_key => $email_value ) {
			if ( empty( $email_value ) ) {
				continue;
			}

			if ( ! is_array( $email_value ) ) {
				$content = $mergtags->decoder( 'wp-changed-user-email-user', $email_value, $params );

				$email_info[ $email_key ] = $content;
			} else {
				$email_info[ $email_key ] = isset( $email_info[ $email_key ] ) ? $email_info[ $email_key ] : array();

				foreach ( $email_value as $value ) {
					$email_info[ $email_key ] .= $value;
				}
			}
		}

		return $email_info;
	}
}

new Wpm_Visual_Wp_Changed_User_Email_User();

