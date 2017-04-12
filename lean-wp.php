<?php
/**
 * Plugin Name: 		LEAN WP
 * Plugin URI:  		https://so-wp.com/plugin/lean-wp/
 * Description:			LEAN WP: WordPress for companies, without the bloat!
 * Version:     		0.0.1
 * Author:				SO WP
 * Author URI:  		https://so-wp.com/plugins/
 * License:    			GPL-3.0+
 * License URI:			http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: 		/languages
 * Text Domain: 		lean-wp
 * Network:     		true
 * GitHub Plugin URI:	https://github.com/senlin/lean-wp
 * GitHub Branch:		develop
 */

// don't load the plugin file directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-lean-wp.php' );
//require_once( 'includes/class-lean-wp-settings.php' );

// Load plugin libraries
//require_once( 'admin/class-lean-wp-admin-api.php' );

// Load other plugins
require_once( 'includes/plugins/disable-embeds.php' );
require_once( 'includes/plugins/disable-emojis.php' );
require_once( 'includes/plugins/disable-json-api.php' );
require_once( 'includes/plugins/mypace-remove-comments-feed-link.php' );
require_once( 'includes/plugins/wp-comment-humility.php' );
require_once( 'includes/plugins/customizer-remove-all-parts/wp-crap.php' );
require_once( 'includes/plugins/move-site-icon-to-settings/msits-site-icon.php' );

/**
 * Returns the main instance of LWP to prevent the need to use globals.
 *
 * @since  0.0.1
 * @return object LWP
 */
/*
function LWP () {
	$instance = LWP::instance( __FILE__, '0.0.1' );

	if ( null === $instance->settings ) {
		$instance->settings = LWP_Settings::instance( $instance );
	}

	return $instance;
}

LWP();
*/
