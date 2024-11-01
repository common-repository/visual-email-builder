<?php //phpcs:ignore
/**
 * Resolves merge tags
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Replace Merge tags from email to required content.
 */
class Wpm_Notification_MergeTags {

	/**
	 * Instance of Class.
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Updating Instance of class.
	 *
	 * @return object
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Wpm_Notification_MergeTags ) ) {
			self::$instance = new Wpm_Notification_MergeTags();
		}
		return self::$instance;
	}

	/**
	 * Replacing Merge Tags with content.
	 *
	 * @param string $email_type Email Type.
	 * @param string $content Email Message.
	 * @param array  $params Extra Mail Perameters.
	 * @return string
	 */
	public function decoder( $email_type, $content, $params = array() ) {
		switch ( $email_type ) {
			case 'wp-signup-admin':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_user_tags( $content, $params['registered_user'] );
				break;
			case 'wp-signup-user':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_signup_user_tags( $content, $params['user'] );
				$content = $this->decode_user_tags( $content, $params['user'] );
				break;
			case 'wp-lost-password-admin':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_user_tags( $content, $params['user'] );
				break;
			case 'wp-lost-password-user':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_user_tags( $content, $params['user'] );
				$content = $this->decode_userlostpassword_tags( $content, $params['user'], $params['key'] );
				break;
			case 'wp-password-changed-user':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_user_tags( $content, $params['user'] );
				break;
			case 'wp-change-user-email-user':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_email_change_user_tags( $content, $params['new_user_data'] );
				break;
			case 'wp-changed-user-email-user':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_email_changed_user_tags( $content, $params['prev_user_data'], $params['new_user_data'] );
				break;
			case 'wp-automatic-core-update':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_automatic_core_update_tags( $content, $params['type'] );
				break;
			case 'wp-change-site-admin-email':
				$current_user = wp_get_current_user();

				$content = $this->decode_website_tags( $content );
				$content = $this->decode_user_tags( $content, $current_user );

				$content = $this->decode_site_admin_email_tags( $content, $params['new_admin_email'] );
				break;
			case 'wp-changed-site-admin-email':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_site_admin_email_changed_tags( $content, $params['old_email'], $params['new_email'] );
				break;
			case 'wp-comment-in-moderation-admin':
				$comment = get_comment( $params['comment_id'] );

				$content = $this->decode_website_tags( $content );
				$content = $this->decode_comment_tags( $content, $params['comment_id'] );
				$content = $this->decode_post_tags( $content, $comment->comment_post_ID );
				$user    = get_userdata( $comment->user_id );
				$content = $this->decode_user_tags( $content, $user );
				break;

			case 'wp-comment-published-post-author':
				$comment = get_comment( $params['comment_id'] );

				$content = $this->decode_website_tags( $content );
				$content = $this->decode_comment_tags( $content, $params['comment_id'] );
				$content = $this->decode_post_tags( $content, $comment->comment_post_ID );
				$user    = get_userdata( $comment->user_id );
				$content = $this->decode_user_tags( $content, $user );
				break;

			case 'wp-request-personal-data-user':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_privacy_confirm_action_tags( $content, $params['request_type'], $params['confirm_url'] );
				break;

			case 'wp-confirmed-personal-data-request-admin':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_privacy_confirmed_user_request_action_tags( $content, $params['request_type'], $params['data_privacy_requests_url'], $params['user_email'] );
				break;
			case 'wp-download-personal-data-user':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_download_personal_data_tags( $content, $params['expiration_date'], $params['export_file_url'] );
				break;
			case 'wp-erased-personal-data-user':
				$content = $this->decode_website_tags( $content );
				$content = $this->decode_erased_personal_data_tags( $content, $params['sitename'] );
				break;

		}

		return $content;
	}

	function decode_signup_user_tags( $content, $user ){
		$user_login = $user->user_login;

		$password_key = get_password_reset_key( $user );
		$reset_link = site_url( "/wp-login.php?action=rp&key=".$password_key."&login=" . rawurlencode( $user_login ) );

		$content = str_replace( '[login_url]', wp_login_url(), $content );
		$content = str_replace( '[password_url]', $reset_link, $content );

		return $content;
	}

	function decode_erased_personal_data_tags( $content, $sitename ){

		$content = str_replace( '[sitename]', $sitename, $content );
		
		return $content;
	}

	function decode_download_personal_data_tags( $content, $expiration_date, $export_file_url ){

		$content = str_replace( '[data_privacy_download_expiry]', $expiration_date, $content );
		$content = str_replace( '[data_privacy_download_url]', $export_file_url, $content );
		
		return $content;
	}

	public function decode_privacy_confirmed_user_request_action_tags( $content, $request_type, $data_privacy_requests_url, $user_email ) {
		$content = str_replace( '[email_user_email]', $user_email, $content );
		$content = str_replace( '[data_request_type]', $request_type, $content );
		$content = str_replace( '[data_privacy_requests_url]', $data_privacy_requests_url, $content );

		return $content;
	}

	function decode_privacy_confirm_action_tags( $content, $request_type, $confirm_url ) {
		$content = str_replace( '[data_request_type]', $request_type, $content );
		$content = str_replace( '[request_confirmation_link]', $confirm_url, $content );

		return $content;
	}

	function decode_post_tags( $content, $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return $content;
		}

		$content = str_replace( '[ID]', $post->ID, $content );
		$content = str_replace( '[post_date]', $this->wpm_visual_format_date( $post->post_date ), $content );
		$content = str_replace( '[post_date_gmt]', $this->wpm_visual_format_date( $post->post_date_gmt ), $content );
		$content = str_replace( '[post_content]', $post->post_content, $content );
		$content = str_replace( '[post_title]', $post->post_title, $content );
		$content = str_replace( '[post_excerpt]', get_the_excerpt( $post ), $content );
		$content = str_replace( '[post_status]', $post->post_status, $content );
		$content = str_replace( '[comment_status]', $post->comment_status, $content );
		$content = str_replace( '[ping_status]', $post->ping_status, $content );
		$content = str_replace( '[post_password]', $post->post_password, $content );
		$content = str_replace( '[post_name]', $post->post_name, $content );
		$content = str_replace( '[post_slug]', $post->post_name, $content );
		$content = str_replace( '[to_ping]', $post->to_ping, $content );
		$content = str_replace( '[pinged]', $post->pinged, $content );

		$content = str_replace( '[post_modified]', $this->wpm_visual_format_date( $post->post_modified ), $content );
		$content = str_replace( '[post_modified_gmt]', $this->wpm_visual_format_date( $post->post_modified_gmt ), $content );
		$content = str_replace( '[post_content_filtered]', $post->post_content_filtered, $content );
		$content = str_replace( '[post_parent]', $post->post_parent, $content );

		$content = str_replace( '[post_parent_permalink]', get_permalink( $post->post_parent ), $content );
		$content = str_replace( '[guid]', $post->guid, $content );
		$content = str_replace( '[menu_order]', $post->menu_order, $content );
		$content = str_replace( '[post_type]', $post->post_type, $content );
		$content = str_replace( '[post_mime_type]', $post->post_mime_type, $content );
		$content = str_replace( '[comment_count]', $post->comment_count, $content );
		$content = str_replace( '[permalink]', get_permalink( $post->ID ), $content );
		$content = str_replace( '[post_type_archive]', get_post_type_archive_link( $post->post_type ), $content );

		$content = str_replace( '[edit_post]', $this->get_edit_post_link( $post->ID, 'return' ), $content );

		$featured_image = '';
		if ( has_post_thumbnail( $post->ID ) ) {
			$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
			if ( is_array( $image_url ) ) {
				$featured_image = $image_url[0];
			}
		}
		$content = str_replace( '[featured_image]', $featured_image, $content );

		$content = str_replace( '[first_image]', $this->get_first_image( $post->post_content ), $content );

		if ( 'future' == $post->post_status ) {
			$content = str_replace( '[post_scheduled_date]', $this->wpm_visual_format_date( $post->post_date ), $content );
			$content = str_replace( '[post_scheduled_date_gmt]', $this->wpm_visual_format_date( $post->post_date_gmt ), $content );
		} else {
			$content = str_replace( '[post_scheduled_date]', 'Published', $content );
			$content = str_replace( '[post_scheduled_date_gmt]', 'Published', $content );
		}

		$categories = wp_get_post_categories( $post_id, array( 'fields' => 'all' ) );
		$content    = str_replace( '[post_category]', implode( ', ', wp_list_pluck( $categories, 'name' ) ), $content );

		if ( count( $categories ) > 0 ) {
			$content = str_replace(
				array(
					'[post_category_slug]',
					'[post_category_description]',
				),
				array(
					$categories[0]->slug,
					$categories[0]->description,
				),
				$content
			);
		}

		$tag_list = implode( ', ', wp_get_post_tags( $post_id, array( 'fields' => 'names' ) ) );
		$content  = str_replace( '[post_tag]', $tag_list, $content );

		$user_info = get_userdata( $post->post_author );
		$content   = str_replace( '[post_author]', $user_info->display_name, $content );

		$content = str_replace( '[author_link]', get_author_posts_url( $post->post_author ), $content );

		if ( $last_id = get_post_meta( $post->ID, '_edit_lock', true ) ) {

			$last_id = explode( ':', $last_id );
			if ( count( $last_id ) > 1 ) {
				$last_id = end( $last_id );
			}

			if ( $post->post_author != $last_id ) {
				$last_user_info = get_userdata( $last_id );
			} else {
				$last_user_info = $user_info;
			}

			$content = str_replace( '[post_update_author]', $last_user_info->display_name, $content );
		}
		$content = str_replace( '[post_term', '[post_term id="' . $post_id . '"', $content );

		return $content;
	}

	/**
	 * Get first image in post.
	 *
	 * @param mixed $post_content
	 *
	 * @return string
	 */
	function get_first_image( $post_content ) {
		if ( preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content, $matches ) ) {
			return $matches[1][0];
		}
	}

	 /**
	  * Retrieves the edit post link for post.
	  *
	  * This is a copy of the built-in function without the user check.
	  *
	  * Can be used within the WordPress loop or outside of it. Can be used with
	  * pages, posts, attachments, and revisions.
	  *
	  * @param int|WP_Post $id      Optional. Post ID or post object. Default is the global `$post`.
	  * @param string      $context Optional. How to output the '&' character. Default '&amp;'.
	  * @return string|null The edit post link for the given post. null if the post type is invalid or does
	  *                     not allow an editing UI.
	  */
	public function get_edit_post_link( $id = 0, $context = 'display' ) {
		if ( ! $post = get_post( $id ) ) {
			return;
		}

		if ( 'revision' === $post->post_type ) {
			$action = '';
		} elseif ( 'display' == $context ) {
			$action = '&amp;action=edit';
		} else {
			$action = '&action=edit';
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object ) {
			return;
		}

		if ( $post_type_object->_edit_link ) {
			$link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
		} else {
			$link = '';
		}

		/**
		 * Filters the post edit link.
		 *
		 * @since 2.3.0
		 *
		 * @param string $link    The edit link.
		 * @param int    $post_id Post ID.
		 * @param string $context The link context. If set to 'display' then ampersands
		 *                        are encoded.
		 */
		return apply_filters( 'get_edit_post_link', $link, $post->ID, $context );
	}

	function wpm_visual_format_date( $date ) {
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$date = date( $date_format . ' ' . $time_format, strtotime( $date ) );

		return $date;
	}

	function decode_comment_tags( $content, $comment_id ) {
		global $wpdb;
		$comment = get_comment( $comment_id );

		$comment_author_domain = '';
		if ( WP_Http::is_ip_address( $comment->comment_author_IP ) ) {
			$comment_author_domain = gethostbyaddr( $comment->comment_author_IP );
		}
		$comments_waiting_in_moderation = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '0'" );
		$comment_moderation_panel_url = admin_url( 'edit-comments.php?comment_status=moderated#wpbody-content' );

		$content = str_replace( '[comment_ID]', $comment->comment_ID, $content );
		$content = str_replace( '[comment_post_ID]', $comment->comment_post_ID, $content );
		$content = str_replace( '[comment_author]', $comment->comment_author, $content );
		$content = str_replace( '[comment_author_email]', $comment->comment_author_email, $content );
		$content = str_replace( '[comment_author_url]', $comment->comment_author_url, $content );
		$content = str_replace( '[comment_moderation_panel_url]', $comment_moderation_panel_url, $content );
		$content = str_replace( '[comments_count_in_moderation]', $comments_waiting_in_moderation, $content );
		$content = str_replace( '[comment_author_IP]', $comment->comment_author_IP, $content );
		$content = str_replace( '[comment_author_domain]', $comment_author_domain, $content );
		$content = str_replace( '[comment_date]', $this->wpm_visual_format_date( $comment->comment_date ), $content );
		$content = str_replace( '[comment_date_gmt]', $this->wpm_visual_format_date( $comment->comment_date_gmt ), $content );
		$content = str_replace( '[comment_content]', get_comment_text( $comment->comment_ID ), $content );
		$content = str_replace( '[comment_karma]', $comment->comment_karma, $content );
		$content = str_replace( '[comment_approved]', str_replace( array( '0', '1', 'spam' ), array( 'Awaiting Moderation', 'Approved', 'Spam' ), $comment->comment_approved ), $content );
		$content = str_replace( '[comment_agent]', $comment->comment_agent, $content );
		$content = str_replace( '[comment_type]', $comment->comment_type, $content );
		$content = str_replace( '[comment_parent]', $comment->comment_parent, $content );
		$content = str_replace( '[user_id]', $comment->user_id, $content );
		$content = str_replace( '[permalink]', get_comment_link( $comment->comment_ID ), $content );
		$content = str_replace( '[comment_moderation_link]', admin_url( 'comment.php?action=editcomment&c=' ) . $comment->comment_ID, $content );
		$content = str_replace( '[comment_moderation_approve]', '<a href="' . wp_nonce_url( admin_url( "comment.php?action=approve&c={$comment->comment_ID}#wpbody-content" ) ) . '">Approve</a>', $content );
		$content = str_replace( '[comment_moderation_spam]', '<a href="' . wp_nonce_url( admin_url( "comment.php?action=spam&c={$comment->comment_ID}#wpbody-content" ) ) . '">Spam</a>', $content );
		$content = str_replace( '[comment_moderation_delete]', '<a href="' . wp_nonce_url( admin_url( "comment.php?action=trash&c={$comment->comment_ID}#wpbody-content" ) ) . '">Delete</a>', $content );

		$parent_comment = get_comment( $comment->comment_parent );
		if ( $parent_comment instanceof WP_Comment ) {
			$content = str_replace( '[comment_parent_content]', $parent_comment->comment_content, $content );
		}

		return $content;
	}

	function decode_site_admin_email_changed_tags( $content, $old_email, $new_email ) {

		$content = str_replace( '[old_email]', $old_email, $content );
		$content = str_replace( '[new_email]', $new_email, $content );

		return $content;
	}

	function decode_site_admin_email_tags( $content, $new_admin_email_data ) {

		$hash = $new_admin_email_data['hash'];
		$new_admin_email = $new_admin_email_data['newemail'];

		$confirmation_link = site_url( '/wp-admin/options.php?adminhash=' . $hash );

		$content = str_replace( '[change_email_link]', $confirmation_link, $content );
		$content = str_replace( '[new_email]', $new_admin_email, $content );

		return $content;
	}

	function decode_automatic_core_update_tags( $content, $type ) {
		$content = str_replace( '[core_update_status]', $type, $content );
		return $content;

	}

	function decode_email_changed_user_tags( $content, $prev_user_data, $new_user_data ) {
		$prev_email = $prev_user_data['user_email'];
		$new_email  = $new_user_data['user_email'];

		$content = str_replace( '[user_old_email]', $prev_email, $content );
		$content = str_replace( '[user_new_email]', $new_email, $content );
		$content = str_replace( '[username]', '###USERNAME###' , $content );


		return $content;
	}

	/**
	 * Change confirmation link tag.
	 *
	 * @param string $content Email Content.
	 * @param array  $new_user_data New Email Data.
	 * @return string
	 */
	public function decode_email_change_user_tags( $content, $new_user_data ) {
		$hash_key = $new_user_data['hash'];
		$new_email = $new_user_data['newemail'];

		$content = str_replace( '[email_change_confirmation_link]', esc_url( admin_url( 'profile.php?newuseremail=' . $hash_key ) ), $content );

		$content = str_replace( '[new_email]', $new_email, $content );
		$content = str_replace( '[username]', '###USERNAME###', $content );
		return $content;
	}

	/**
	 * Change lost Password Merge Tags.
	 *
	 * @param string $content Email Message.
	 * @param object $user WP user object.
	 * @param string $key lostpassword Key.
	 * @return string
	 */
	public function decode_userlostpassword_tags( $content, $user, $key ) {
		$user_login = $user->user_login;
		$reset_link = site_url( "/wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ) );
		$content    = str_replace( '[password_reset_link]', $reset_link, $content );

		return $content;
	}
	/**
	 * Change website merge tags
	 *
	 * @param string $content message of email.
	 * @return string
	 */
	public function decode_website_tags( $content ) {
		
		$content = str_replace( '[global_site_title]', get_bloginfo( 'name' ), $content );
		$content = str_replace( '[global_site_tagline]', get_bloginfo( 'description' ), $content );
		$content = str_replace( '[global_site_url]', get_bloginfo( 'url' ), $content );

		$content = str_replace( '[current_time]', current_time( get_option( 'time_format' ) ), $content );
		$content = str_replace( '[current_date]', date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) ), $content );
		$content = str_replace( '[admin_email]', get_option( 'admin_email' ), $content );

		return $content;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $content message of email.
	 * @param object $registered_user Data related to user provided by hook.
	 * @return string
	 */
	public function decode_user_tags( $content, $registered_user ) {

		global $wp_roles;
		
		if ( ! $registered_user instanceof WP_User ) {
			return $content;
		}

		$content = str_replace( '[user_id]', $registered_user->ID, $content );
		$content = str_replace( '[user_login]', $registered_user->user_login, $content );
		$content = str_replace( '[display_name]', $registered_user->display_name, $content );

		$content = str_replace( '[user_email]', $registered_user->user_email, $content );
		$content = str_replace( '[user_nicename]', $registered_user->user_nicename, $content );
		$content = str_replace( '[user_url]', $registered_user->user_url, $content );
		$content = str_replace( '[user_registered]', $registered_user->user_registered, $content );
		$content = str_replace( '[user_firstname]', $registered_user->first_name, $content );
		$content = str_replace( '[user_lastname]', $registered_user->last_name, $content );
		$content = str_replace( '[user_description]', $registered_user->description, $content );
		$content = str_replace( '[nickname]', $registered_user->nickname, $content );
		$content = str_replace( '[user_avatar]', get_avatar_url( $registered_user->ID ), $content );

		$roles   = $this->get_user_roles_label( $registered_user->roles );
		$content = str_replace( '[user_role]', $roles, $content );

		$user_capabilities = $this->format_user_capabilities( $registered_user->wp_capabilities );
		if ( ! empty( $user_capabilities ) ) {

			$content = str_replace( '[wp_capabilities]', $user_capabilities, $content );
		}

		return $content;
	}

	/**
	 * Convert array of capabilities to string.
	 *
	 * @param array $wp_capabilities Capabilities of user.
	 * @return string
	 */
	public function format_user_capabilities( $wp_capabilities ) {
		$capabilities = array();
		if ( is_array( $wp_capabilities ) ) {
			foreach ( $wp_capabilities as $capability => $enabled ) {
				if ( $enabled ) {
					$capabilities[] = $capability;
				}
			}
		}
		$capabilities = implode( ', ', $capabilities );
		return $capabilities;
	}

	/**
	 * Converting user roles array to string.
	 *
	 * @param array $roles User roles.
	 * @return string
	 */
	public function get_user_roles_label( $roles ) {
		$role_lables = array();

		foreach ( $roles as $role ) {
			array_push( $role_lables, $role );
		}
		$role_lables = implode( ', ', $role_lables );
		return $role_lables;
	}



} // class ends here

/**
 * Calling the class.
 *
 * @return object
 */
function wpm_notification_merge_tags() {
	return Wpm_Notification_MergeTags::instance();
}
wpm_notification_merge_tags();
