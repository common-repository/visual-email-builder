<?php // phpcs:ignore

/**
 * Modify email triggerd after site admin is changed.
 */
class Wpm_Visual_Wp_Changed_Site_Admin_Email {
	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_filter( 'site_admin_email_change_email', array( $this, 'modify_email' ), 9999, 3 );
	}

	/**
	 * Modify email.
	 *
	 * @param array  $email_info Email Data.
	 * @param string $old_email The old site admin email address.
	 * @param string $new_email The new site admin email address.
	 * @return array
	 */
	public function modify_email( $email_info, $old_email, $new_email ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array(
			'old_email' => $old_email,
			'new_email' => $new_email,
		);

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-changed-site-admin-email' );
		
		if ( empty( $email_data ) ) {
			return $email_info;
		}

		$email_info['headers'] = 'Content-Type: text/html; charset=UTF-8 ' . "\r\n";

		foreach ( $email_data as $email_key => $email_value ) {
			if ( empty( $email_value ) ) {
				continue;
			}

			if ( ! is_array( $email_value ) ) {

				$content = $mergtags->decoder( 'wp-changed-site-admin-email', $email_value, $params );

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

new Wpm_Visual_Wp_Changed_Site_Admin_Email();
