<?php // phpcs:ignore

/**
 * Create metaboxes for Notification post types
 */
class Wpm_Notification_Metaboxes {

	/**
	 * Excecute hooks
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'force_publish_status_on_save'), 10, 2 );
		add_filter( 'script_loader_src', array( $this, 'kill_autosave_on_postype'), 10, 2 );

	}

	function kill_autosave_on_postype( $src, $handle ) {
		global $typenow;
		if( 'autosave' != $handle || $typenow != 'wpmnotifications' )
			return $src;
		return '';
	}

	function force_publish_status_on_save( $data, $postarr ) {

		if( $data['post_type'] === 'wpmnotifications' && $data['post_status'] === 'draft' ) {
			$data['post_status'] = 'publish';
		}

		return $data;
	}

	/**
	 * Register metaboxes for wpmnotifications post type
	 *
	 * @return void
	 */
	public function register_meta_box() {

		add_meta_box(
			'wpmnotifications_meta_box',
			esc_html__(
				'Visual Notification Builder',
				'wpm-notifications'
			),
			array( $this, 'render_wpmnotification_settings' ),
			'wpmnotifications',
			'advanced',
			'high'
		);

		add_meta_box(
			'wpmnotifications_submitdiv',
			esc_html__(
				'Save Notification',
				'wpm-notifications'
			),
			array( $this, 'render_custom_submitdiv' ),
			'wpmnotifications',
			'side',
			'core'
		);

		remove_meta_box(
			'submitdiv',
			'wpmnotifications',
			'side'
		);

	}

	/**
	 * Save notifications custom meta data
	 *
	 * @param int $post_id Id of post for which data is saved.
	 * @return void
	 */
	public function save_post( $post_id ) {

		$post_data =  $_POST;

		if ( ! isset( $post_data['_wpm_notifications_settings_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $post_data['_wpm_notifications_settings_nonce'], 'wpm_notifications_settings' ) ) {
			return;
		}

		$post_type = get_post_type( $post_id );

		if ( 'wpmnotifications' !== $post_type || ! isset( $post_data['wpmNotificationsData'] ) ) {
			return;
		}

		if ( isset( $post_data['notification-staus'] ) ) {
			$notification_status = wp_filter_post_kses($post_data['notification-staus'] );
		}

		$notification_data = wp_filter_post_kses( $post_data['wpmNotificationsData'] );

		update_post_meta( $post_id, 'wpmNotificationsStatus', $notification_status );
		update_post_meta( $post_id, 'wpmNotificationsData', $notification_data );

		if ( 'true' === $post_data['send-test-email'] ) { // send test email.
		
			$email_data = Wpm_Notification_Email_Helper::send_email( $post_id, $post_data['test-email-id'] );
		}

	}
	
	/**
	 * Render Custom save post metabox
	 *
	 * @param object $post post object.
	 * @return void
	 */
	public function render_custom_submitdiv( $post ) {

		$notification_status = get_post_meta( $post->ID, 'wpmNotificationsStatus', true );
		$current_user        = wp_get_current_user();
		$current_user_email  = $current_user->user_email;

		if ( empty( $notification_status ) ) {
			$notification_status = 'true';
		}

		?>
		<div class="submitbox" id="submitpost">

			<div class="wpm-notification-status">
				<label>
					<input type="radio" name="notification-staus" value="true" <?php checked( $notification_status, 'true', true ); ?> >
					Notification Enabled
				</label>
				<br />
				<label>
					<input type="radio" name="notification-staus" value="false" <?php checked( $notification_status, 'false', true ); ?> >
					Notification Disabled
				</label>
			</div>
			<br />
			<div class="wpm-send-mail-container" style="padding: 0px 0px 12px 0px;">
			<input type="hidden" name="send-test-email" id="send-test-email" value="false">
			<input type="email" name="test-email-id" style="margin-bottom: 10px;" value="<?php echo esc_html( $current_user_email ); // phpcs:ignore ?>" required/> 
				<input type="submit" class="button button-secondary button-large button button-primary button-large" id="test-email" value="Send Me a Test Email">
			</div>
			<div id="major-publishing-actions">
				<div id="delete-action">
						<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>">Move to Trash</a>
					</div>

				<div id="publishing-action">
					<span class="spinner"></span>
						<input name="original_publish" type="hidden" id="original_publish" value="Update">

						<input type="submit" name="save" id="publish" class="button button-primary button-large" value="Update">
				</div>
				<div class="clear"></div>
			</div>



		</div>
		<!-- #submitpost -->
		<?php
	}

	/**
	 * Metabox that contains all the settings for post
	 *
	 * @param object $post post object.
	 * @return void
	 */
	public function render_wpmnotification_settings( $post ) {
		$post_id = $post->ID;
		wp_nonce_field( 'wpm_notifications_settings', '_wpm_notifications_settings_nonce' );

		$wpm_notifications = array(
			'editor' => array(),
			'email'  => array(),
		);

		$wpm_notifications = get_post_meta( $post_id, 'wpmNotificationsData', true );

		$wpm_notifications = ! empty( $wpm_notifications ) ? addslashes( $wpm_notifications ) : '{}';

		$saved_template_ids = Wpm_Notification_Templates::get_template_ids();
		$saved_template_ids = apply_filters( 'wpm_notifications_add_template_ids', $saved_template_ids );
			
		$saved_template_ids = wp_json_encode( $saved_template_ids );

		$wpm_notification_templates = array(
			'storeUrl'    => WPM_NOTIFICATION_STORE_URL,
			'templateIds' => $saved_template_ids,
		);
		
		
		echo '<script>var wpmNotifications="' . wp_filter_post_kses( $wpm_notifications )  . '";
		        var wpmNotificationTemplates=' . wp_json_encode( $wpm_notification_templates ) . ';
		</script>';
		?>
		<div id='wpm-notifications-root'></div>
		<?php
	}
}

new Wpm_Notification_Metaboxes();
