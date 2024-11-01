<?php
/**
 * Class to modify Automatic Core Update Email.
 */
class Wpm_Visual_Wp_Automatic_Core_Update {

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_filter( 'auto_core_update_email', array( $this, 'modify_email' ), 9999, 4 );
	}

	/**
	 * Modify Email
	 *
	 * @param array  $email Container Email Data.
	 * @param string $type The type of email being sent. Can be one of 'success', 'fail', 'manual', 'critical'.
	 * @param object $core_update The update offer that was attempted.
	 * @param mixed  $result The result for the core update.
	 * @return array
	 */
	public function modify_email( $email, $type, $core_update, $result ) {

		$mergtags = wpm_notification_merge_tags();

		$params = array(
			'type'        => $type
		);

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-automatic-core-update' );

		if( empty($email_data) ){
			return $email;
		}

		$email['headers'] = 'Content-Type: text/html; charset=UTF-8 ' . "\r\n";

		foreach ( $email_data as $email_key => $email_value ) {
			if ( empty( $email_value ) ) {
				continue;
			}

			if ( ! is_array( $email_value ) ) {

				$content = $mergtags->decoder( 'wp-automatic-core-update', $email_value, $params );

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

new Wpm_Visual_Wp_Automatic_Core_Update();
