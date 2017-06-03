<?php

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_dashboard_setup', array( 'LEAN_WP_Dashboard_Widget','init' ) );

class LEAN_WP_Dashboard_Widget {

    /**
     * Hook to wp_dashboard_setup to add the widget.
     */
    public static function init() {
        //Register the widget...
		wp_add_dashboard_widget( 'leanwp-dashboard-widget', esc_html__( 'Site information', 'lean-wp'), array( 'LEAN_WP_Dashboard_Widget', 'widget' ) );
    }

    /**
     * Load the widget code
     */
    public static function widget() {
        require_once( 'widget.php' );
    }

}
