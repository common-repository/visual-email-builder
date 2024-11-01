<?php // phpcs:ignore

class Wpm_Visual_Wp_Plugin_Theme_Update {
	function __construct() {
		add_filter( 'auto_plugin_theme_update_email', array( $this, 'modify_email' ), 9999, 4 );

	}
	function modify_email( $email, $type, $core_update, $result ) {

		$email_data = Wpm_Notification_Email_Helper::get_email_data( 'wp-automatic-plugin-theme-update' );

		if ( empty( $email_data ) ) {
			return $email;
		}

		$email['headers'] = 'Content-Type: text/html; charset=UTF-8 ' . "\r\n";

		foreach ( $email_data as $email_key => $email_value ) {
			if ( empty( $email_value ) ) {
				continue;
			}

			if ( ! is_array( $email_value ) ) {

				$email[ $email_key ] = $email_value;
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

new Wpm_Visual_Wp_Plugin_Theme_Update();
