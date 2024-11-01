<?php

class Wpm_Notification_License_Page{

	function __construct(){
		add_action('admin_menu',array($this,'register_menu') );
		add_action( 'admin_init', array( $this, 'setting_fields' ) );
	}

	public function register_menu(){
		// add_menu_page(  'Email Builder', 'Styles & Layouts for GF', 'manage_options', 'wpm_notification_licenses' );
		add_submenu_page( 'edit.php?post_type=wpmnotifications', 'Licenses', 'Licenses', 'manage_options', 'wpm_notification_licenses', array( $this, 'license_settings' ) );
	}

	public function license_settings(){

		?>
			<!-- Create a header in the default WordPress 'wrap' container -->
    <div class="wrap">

        <!-- Make a call to the WordPress function for rendering errors when settings are saved. -->
        <?php settings_errors(); ?>
        <!-- Create the form that will be used to render our options -->
        <form method="post" action="options.php">
            <?php settings_fields( 'wpm_notification_licenses' ); ?>
            <?php do_settings_sections( 'wpm_notification_licenses' ); ?>
            <?php submit_button(); ?>
        </form>

    </div><!-- /.wrap -->
	<?php
	}


	function setting_fields(){
		// If settings don't exist, create them.
		if ( false == get_option( 'wpm_notification_licenses' ) ) {
			add_option( 'stla_licenses' );
		}


		add_settings_section(
			'wpm_notification_licenses_section',
			'Add-On Licenses',
			array( $this, 'section_callback' ),
			'wpm_notification_licenses'
		);

		do_action('wpm_notification_license_fields',$this);

		//register settings
		register_setting( 'wpm_notification_licenses', 'wpm_notification_licenses' );

	}

	public function section_callback() {

		echo '<h4> Licence Fields will automatically appear once you install addons for \'Visual Email Builder\'. You can check all the available addons <a href="https://wpemailbuilder.com">here</a></h4>';
	}


}

new Wpm_Notification_License_Page();