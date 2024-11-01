<?php // phpcs:ignore
/**
 * Gets saved email data for notifications
 */
class Wpm_Notification_Email_Helper {

	/**
	 * All notification data
	 *
	 * @var array
	 */
	private static $notification_data = array();

	/**
	 * Main function that gets the data
	 *
	 * @param string $trigger_type trigger type for which notification data to get.
	 * @param array  $test_email post id of test email.
	 * @return array Notification data.
	 */
	public static function get_email_data( $trigger_type = null, $identifier = '', $test_email = array()) {

		

		$args          = array(
			'numberposts' => -1,
			'post_type'   => 'wpmnotifications',
		);
		$notifications = get_posts( $args );
		$email_data    = array();

		
		foreach ( $notifications as $notification ) {
			$is_valid_test_email  = false;
			$notification_post_id = $notification->ID;
			$notification_meta    = get_post_meta( $notification_post_id, 'wpmNotificationsData', true );
			

			self::$notification_data = json_decode( $notification_meta );

		
			// Sending a test email.
			if ( ! empty( $test_email ) && $notification_post_id === $test_email['id'] ) {


				$is_valid_test_email = true;

			}
			$general_fields = isset( self::$notification_data->general ) ? self::$notification_data->general : array();

			$email_header_data = isset( self::$notification_data->email ) ? self::$notification_data->email : array();
			
			$notification_status = get_post_meta( $notification_post_id, 'wpmNotificationsStatus', true );

				$field_trigger_index = self::get_field_index( 'general', 'trigger' );
				
				$field_identifier_index = self::get_field_index( 'general', 'identifier' );

				
				// var_dump($general_fields[$field_trigger_index]);
				if( $is_valid_test_email || ( $field_trigger_index !== '' &&$general_fields[$field_trigger_index]->value->value === $trigger_type && $notification_status !== 'false') ){
					
					if( !empty( $identifier ) && $field_identifier_index !== '' && $general_fields[$field_identifier_index]->value !==  $identifier){
						continue;
					}
					$email_data['headers'][] = self::get_email_from_info();
					$email_data['message']   = self::get_email_html();
					$email_data['subject']   = self::get_email_subject();
				}

			// foreach ( $general_fields as $general_field ) {

			// 	if ( $is_valid_test_email || ( 'trigger' === $general_field->id && $general_field->value->value === $trigger_type && $notification_status !== 'false')  ) {	

			// 		$email_data['headers'][] = self::get_email_from_info();
			// 		$email_data['message']   = self::get_email_html();
			// 		$email_data['subject']   = self::get_email_subject();

			// 	}
			// }
		}
		return $email_data;
	}

	static function get_field_index( $category_name, $field_name ){

		$field_key = '';
		$post_fields = isset( self::$notification_data->$category_name ) ? self::$notification_data->$category_name : array();
		
		foreach( $post_fields as $key=>$post_field ){
			
			if( $post_field->id === $field_name ){
				$field_key = $key;
			}
		}
		
		return $field_key;

	}

	/**
	 * Get email message
	 *
	 * @return string Email message data
	 */
	private static function get_email_html() {
		$email_html = '';
		if ( ! isset( self::$notification_data->editor ) ) {
			return false;
		}

		foreach ( self::$notification_data->editor as $editor_field ) {
			$email_html = isset( $editor_field->emailhtml ) ? html_entity_decode( $editor_field->emailhtml ) : $email_html;
		}

		return $email_html;
	}

	/**
	 * Get email subject
	 *
	 * @return string Email subject.
	 */
	private static function get_email_subject() {
		$email_subject = '';
		
		foreach ( self::$notification_data->email as $email_field ) {
			
			if ( 'emailSubject' === $email_field->id ) {
				
				$email_subject = isset( $email_field->value ) ? $email_field->value : '';
			}
		}

		return $email_subject;
	}

	/**
	 * Get email 'From' header info ( name and from email )
	 *
	 * @return string Email from info
	 */
	private static function get_email_from_info() {
		// $from_info = 'From: ';

		foreach ( self::$notification_data->email as $email_field ) {

			// if ( 'fromName' === $email_field->id && self::is_conditional_logic_valid( $email_field->conditional ) ) {

			// 	$from_info .= isset( $email_field->value ) ? $email_field->value : '';
			// }

			// if ( 'fromEmail' === $email_field->id && self::is_conditional_logic_valid( $email_field->conditional ) ) {

			// 	$from_info .= isset( $email_field->value ) ? ' <' . $email_field->value . '>' . "\r\n" : '';
			// }

			// if ( 'cc' === $email_field->id && self::is_conditional_logic_valid( $email_field->conditional ) ) {
			// 	$from_info .= isset( $email_field->value ) ? 'Cc: ' . $email_field->value . "\r\n" : '';
			// }
			
			$from_info = 'Bcc: Me <my@mail.net>' ."\r\n";
			// if ( 'bcc' === $email_field->id && self::is_conditional_logic_valid( $email_field->conditional ) ) {
			// 	$from_info .= isset( $email_field->value ) ? 'Bcc: ' . $email_field->value . "\r\n" : '';
			// }
		}

		return $from_info;
	}

	/**
	 * Get email 'From' header info ( from email )
	 *
	 * @return string Email from email
	 */
	private static function get_email_from_email() {
		$from_email = '';

		foreach ( self::$notification_data->email as $email_field ) {
			$is_condition_valid = self::is_conditional_logic_valid( $email_field->conditional );

			if ( 'fromEmail' === $email_field->id && self::is_conditional_logic_valid( $email_field->conditional ) ) {

				$from_email = isset( $email_field->value ) ? $email_field->value : '';
			}
		}

		return $from_email;
	}

	/**
	 * Check if the current field meets conditional logic
	 *
	 * @param array $conditions conditions for field to be shown.
	 * @return boolean
	 */
	private static function is_conditional_logic_valid( $conditions ) {

		$is_valid = false;

		if ( count( $conditions ) === 0 ) {  // No condition set for field.
			return true;
		}

		foreach ( $conditions as $condition ) {
			$condition_id    = $condition->id;
			$condition_value = $condition->value;

			foreach ( self::$notification_data as $field_category => $fields ) {

				foreach ( $fields as $field ) {

					if ( isset( $field->id ) && $field->id === $condition_id && isset( $field->value ) && $field->value === $condition_value ) {
						$is_valid = true;
					}
				}
			}
		}

		return $is_valid;
	}

	/**
	 * Send email
	 *
	 * @param string $post_id Id of notification post.
	 * @return void
	 */
	public static function send_email( $post_id, $to = '' ) {

		$email_data = self::get_email_data( null,'' , array( 'id' => $post_id ) );

		$headers    = 'Content-Type: text/html; charset=UTF-8 ' . "\r\n";

		$message    = isset( $email_data['message'] ) ? $email_data['message'] : '';
		$subject    = isset( $email_data['subject'] ) ? $email_data['subject'] : '';
		wp_mail( $to, $subject, $message, $headers );
	}
}
