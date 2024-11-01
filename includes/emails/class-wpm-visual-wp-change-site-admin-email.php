<?php
/**
 * Class to modify admin change email.
 */

class Wpm_Visual_Wp_Change_Site_Admin_Email {
	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_filter( 'new_admin_email_content', array( $this, 'change_admin_email' ), 9999, 2 );
	}

	/**
	 * Change admin email.
	 *
	 * @param string $email_text Text in the email.
	 * @param array  $new_admin_email Data relating to the new site admin email address.
	 * @return string
	 */
	public function change_admin_email( $email_text, $new_admin_email ) {

		$mergtags = wpm_notification_merge_tags();
		$params   = array( 'new_admin_email' => $new_admin_email );

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-change-site-admin-email' );

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

		$email_text = $mergtags->decoder( 'wp-change-site-admin-email', $email_text, $params );

		return $email_text;
	}

}

new Wpm_Visual_Wp_Change_Site_Admin_Email();
