<?php


class Wpm_Notification_Templates{

    private static $template_names = array( 'basic', 'trello' );

    public static function get_notification_template_id( $prefix, $trigger) {
        $template_id = $prefix.'_'.$trigger;

        return $template_id;
    }

    public static function get_notification_triggers() {
        $triggers = array();

        if( class_exists( 'Wpm_Visual_Notification_Builder' ) ) {
            $triggers = Wpm_Visual_Notification_Builder::get_notification_triggers();
        }

        return $triggers;
    }


    public static function get_templates( ) {

        $templates = array();

        $notification_triggers = self::get_notification_triggers();
     

        foreach( self::$template_names as $template_name ) {

            foreach( $notification_triggers as $trigger ) {
                $template_id = self::get_notification_template_id( $template_name, $trigger );
                
                if( file_exists( WPM_NOTIFICATION_DIR.'/includes/templates/'.$template_name.'/'.$trigger.'.html')) {

                    $templates[$template_id] = file_get_contents(WPM_NOTIFICATION_DIR.'/includes/templates/'.$template_name.'/'.$trigger.'.html');
                }

            }
            
        }
        return $templates;
    }

    public static function get_template_ids( ) {
        
        $template_ids = array();
        $notification_triggers = self::get_notification_triggers();


        foreach( self::$template_names as $template_name ) {

            foreach( $notification_triggers as $trigger ) {
                $template_id = self::get_notification_template_id( $template_name, $trigger );
                if( file_exists( WPM_NOTIFICATION_DIR.'/includes/templates/'.$template_name.'/'.$trigger.'.html')) {
                    $template_ids[] = $template_id;
                }

            }
            
        }
        // $template_ids = array_merge( $template_ids, $this->wp_template_ids );
        return $template_ids;
    }

}
