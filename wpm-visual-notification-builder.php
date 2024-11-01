<?php // phpcs:ignore
/**
 * Plugin Name: Visual Email Builder
 * Description: Easily design WordPress Emails
 * Author: Sushil Kumar
 * Author URI: https://wpemailbuilder.com
 * Version: 1.2
 * License: GPLv2
 *
 */

// don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// set constants for plugin directory and plugin url.
define( 'WPM_NOTIFICATION_DIR', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) );
define( 'WPM_NOTIFICATION_URL', plugins_url() . '/' . basename( dirname( __FILE__ ) ) );
define( 'WPM_NOTIFICATION_VERSION', '1.2' );
define( 'WPM_NOTIFICATION_STORE_URL', 'https://wpemailbuilder.com' );


if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	include_once WPM_NOTIFICATION_DIR . '/includes/admin-menu/EDD_SL_Plugin_Updater.php';
}


require_once WPM_NOTIFICATION_DIR . '/includes/admin-menu/licenses.php';



/**
 * Main class
 */
class Wpm_Visual_Notification_Builder {

	private static $notification_triggers = array( 'wp-automatic-core-update', 'wp-changed-site-admin-email', 'wp-changed-user-email-user', 'wp-password-changed-user', 'wp-change-site-admin-email', 'wp-comment-in-moderation-admin', 'wp-lost-password-admin', 'wp-lost-password-user', 'wp-signup-admin', 'wp-signup-user', 'wp-comment-published', 'wp-request-personal-data-user', 'wp-confirmed-personal-data-request-admin', 'wp-download-personal-data-user', 'wp-change-user-email-user', 'wp-erased-personal-data-user', 'wp-comment-published-post-author' );


	/**
	 * Excecute hooks
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'include_neccessary_files' ) );

		add_action( 'admin_print_styles-post.php', array( $this, 'enqueue_styles_scripts' ) );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'enqueue_styles_scripts' ) );

		add_action( 'init', array( $this, 'notification_cpt' ) );

		add_action( 'wp_ajax_wpm_notifications_load_template', array( $this, 'wpm_notifications_load_template' ) );

		add_action( 'wp_ajax_wpm_notifications_template_license_verification', array( $this, 'wpm_notifications_template_license_verification' ) );

	}


	public static function get_notification_triggers() {
		self::$notification_triggers = apply_filters( 'wpm_notification_add_triggers', self::$notification_triggers );

		return self::$notification_triggers;
	}


	/**
	 * Include helper and email files
	 *
	 * @return void
	 */
	function include_neccessary_files() {

		// var_dump( get_user_meta(1) );

		$helper_files = array( 'email-helper', 'mergetags', 'metaboxes', 'templates' );

		$email_files = self::get_notification_triggers();

		foreach ( $helper_files as $file ) {
			if ( file_exists( WPM_NOTIFICATION_DIR . '/includes/helpers/class-wpm-notification-' . $file . '.php' ) ) {
				require_once WPM_NOTIFICATION_DIR . '/includes/helpers/class-wpm-notification-' . $file . '.php';
			}
		}

		foreach ( $email_files as $file ) {
			if ( file_exists( WPM_NOTIFICATION_DIR . '/includes/emails/class-wpm-visual-' . $file . '.php' ) ) {
				require_once WPM_NOTIFICATION_DIR . '/includes/emails/class-wpm-visual-' . $file . '.php';
			}
		}

	}

	/**
	 * Load template on selection
	 *
	 * @return void
	 */
	public function wpm_notifications_load_template() {
		$post_data = $_POST;
		if ( ! isset( $post_data['nonce'] ) || ! wp_verify_nonce( $post_data['nonce'], 'wpm_notifications_ajax_nonce' ) ) {
			wp_send_json(
				array(
					'valid'   => 'false',
					'message' => 'Nonce verification failed',
				)
			);
		}

		$template_id = isset( $post_data['id'] ) ? sanitize_text_field( $post_data['id'] ) : false;

		$templates = Wpm_Notification_Templates::get_templates();

		$templates = apply_filters( 'wpm_notifications_load_templates', $templates );

		$template = isset( $templates[ $template_id ] ) ? html_entity_decode( $templates[ $template_id ] ) : '';


		wp_send_json( $template );
	}

	/**
	 * Verify if the license key entered is correct or not
	 *
	 * @return void
	 */
	public function wpm_notifications_template_license_verification() {
		$post_data = $_POST;

		if ( ! isset( $post_data['nonce'] ) || ! wp_verify_nonce( $post_data['nonce'], 'wpm_notifications_ajax_nonce' ) ) {
			wp_send_json(
				array(
					'valid'   => 'false',
					'message' => 'Nonce verification failed',
				)
			);
		}

		if ( ! isset( $post_data['templateId'] ) ) {
			wp_send_json(
				array(
					'valid'   => 'false',
					'message' => 'Template id not set',
				)
			);
		}

		if ( empty( $post_data['key'] ) ) {
			wp_send_json(
				array(
					'valid'   => 'false',
					'message' => 'Server Error. Please try again',
				)
			);
		}

		$template_id = sanitize_text_field( $post_data['templateId'] );
		$key         = sanitize_text_field( $post_data['key'] );

		$api_params = array(
			'id'  => $template_id,
			'key' => $key,
		);

		$request_verification_url = WPM_NOTIFICATION_STORE_URL . '/wp-json/wpm-notifications/v1/template';

		$response = wp_remote_get(
			add_query_arg( $api_params, $request_verification_url ),
			array(
				'timeout'   => 20,
				'sslverify' => false,
			)
		);
		$result   = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $result['license'] ) && 'valid' === $result['license'] ) {
			$saved_templates                 = get_option( 'wpm_notifications_templates' );
			$saved_templates[ $template_id ] = wp_kses_post( $result['components'] );

			update_option( 'wpm_notifications_templates', $saved_templates );

			wp_send_json(
				array(
					'valid'   => 'true',
					'message' => 'Successfully Verfied',
				)
			);
		} else {
			if ( isset( $result['license'] ) ) {
				wp_send_json(
					array(
						'valid'   => 'false',
						'message' => $result['license'] . ' license key',
					)
				);
			}
			wp_send_json(
				array(
					'valid'   => 'false',
					'message' => 'Server Error. Please try again',
				)
			);
		}
	}

	/**
	 * Create custom post type
	 *
	 * @return void
	 */
	public function notification_cpt() {
		$labels = array(
			'name'               => _x( 'Visual Notifications', 'post type general name', 'your-plugin-textdomain' ),
			'singular_name'      => _x( 'Notification', 'post type singular name', 'your-plugin-textdomain' ),
			'menu_name'          => _x( 'Visual Notifications', 'admin menu', 'your-plugin-textdomain' ),
			'name_admin_bar'     => _x( 'Visual Notifications', 'add new on admin bar', 'your-plugin-textdomain' ),
			'add_new'            => _x( 'Add New', 'wpmnotifications', 'your-plugin-textdomain' ),
			'add_new_item'       => __( 'Add New Notification', 'your-plugin-textdomain' ),
			'new_item'           => __( 'New Notification', 'your-plugin-textdomain' ),
			'edit_item'          => __( 'Edit Notification', 'your-plugin-textdomain' ),
			'view_item'          => __( 'View Notification', 'your-plugin-textdomain' ),
			'all_items'          => __( 'All Notifications', 'your-plugin-textdomain' ),
			'search_items'       => __( 'Search Notifications', 'your-plugin-textdomain' ),
			'parent_item_colon'  => __( 'Parent Notifications:', 'your-plugin-textdomain' ),
			'not_found'          => __( 'No Notifications found.', 'your-plugin-textdomain' ),
			'not_found_in_trash' => __( 'No Notifications found in Trash.', 'your-plugin-textdomain' ),
		);

		$args = array(
			'labels'                => $labels,
			'description'           => __( 'Visual Notification Builder.', 'your-plugin-textdomain' ),
			'public'                => false,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'wpmnotifications' ),
			'capability_type'       => 'post',
			'has_archive'           => true,
			'hierarchical'          => false,
			'menu_position'         => null,
			'show_in_rest'          => true,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'supports'              => array( 'title' ),
		);

		register_post_type( 'wpmnotifications', $args );
	}

	public function get_dependencies( ) {
		$dependencies = array( 'underscore', 'wpm_notifications_select2' );

		
		if( is_plugin_active( 'wpm-visual-notification-gravity-forms/wpm-visual-notification-gravity-forms.php') ) {
			array_push( $dependencies, 'wpm_notifications_gravityforms_admin' );
		}
		 return $dependencies;
	}

	/**
	 * Enqueue admin styles and scripts
	 *
	 * @return void
	 */
	public function enqueue_styles_scripts() {

		$js_plugin_url_var = 'var wpmNotificationPluginBuildUrl = "'.WPM_NOTIFICATION_URL.'/build/";';

		wp_enqueue_style( 'wpm_notifications_admin', WPM_NOTIFICATION_URL . '/build/static/css/admin.css', array(), WPM_NOTIFICATION_VERSION );
		wp_enqueue_style( 'wpm_notifications_select2', WPM_NOTIFICATION_URL . '/includes/css/select2.min.css', array(), WPM_NOTIFICATION_VERSION );

		wp_enqueue_script( 'wpm_notifications_select2', WPM_NOTIFICATION_URL . '/includes/js/select2.min.js', array( 'jquery' ), WPM_NOTIFICATION_VERSION, true );

		$main_dependencies = $this->get_dependencies();
	
		wp_enqueue_script( 'wpm_notifications_admin', WPM_NOTIFICATION_URL . '/build/static/js/admin.js', $main_dependencies, WPM_NOTIFICATION_VERSION, true );

		wp_add_inline_script( 'wpm_notifications_admin', $js_plugin_url_var, 'before');

		wp_enqueue_script( 'wpm_notifications_custom_admin', WPM_NOTIFICATION_URL . '/includes/js/admin.js', '', WPM_NOTIFICATION_VERSION, true );

		wp_localize_script(
			'wpm_notifications_admin',
			'wpm_notifications_ajax',
			array(
				'nonce' => wp_create_nonce( 'wpm_notifications_ajax_nonce' ),
			)
		);
	}

} //class ends here

add_action( 'plugins_loaded', 'wpm_visual_notification_builder_callback' );

/**
 * Creates an instance of class
 *
 * @return void
 */
function wpm_visual_notification_builder_callback() {
	new Wpm_Visual_Notification_Builder();
}
