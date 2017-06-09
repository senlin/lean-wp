<?php
/*
 * Plugin Name: 		LEAN WP
 * Version:     		1.0.0
 * Plugin URI:  		https://so-wp.com/plugin/lean-wp/
 * Description:			LEAN WP: WordPress for company websites, without the bloat!
 * Network:     		true

 * Author:				SO WP
 * Author URI:  		https://so-wp.com/plugins/

 * Requires at least:	4.7
 * Tested up to:		4.8

 * License:    			GPL-3.0+
 * License URI:			http://www.gnu.org/licenses/gpl-3.0.txt

 * Text Domain: 		lean-wp

 * GitHub Plugin URI:	https://github.com/senlin/lean-wp
 * GitHub Branch:		master

 * @package WordPress
 * @author SO WP
 * @since 1.0.0
 */

// don't load the plugin file directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-lean-wp.php' );
//require_once( 'includes/class-lean-wp-settings.php' );

// Load plugin libraries
//require_once( 'includes/lib/class-lean-wp-admin-api.php' );
//require_once( 'includes/lib/class-lean-wp-post-type.php' );
//require_once( 'includes/lib/class-lean-wp-taxonomy.php' );
require_once( 'includes/lib/class-lean-wp-comments2posts.php' );
require_once( 'includes/lib/class-lean-wp-disable-embeds.php' );
require_once( 'includes/lib/class-lean-wp-disable-emojis.php' );
require_once( 'includes/lib/class-lean-wp-disable-rest-api.php' );
require_once( 'includes/lib/class-lean-wp-remove-comments-feed-link.php' );
require_once( 'includes/lib/class-lean-wp-dashboard-widget.php' );

// Load WP_Dependency_Installer
include_once( __DIR__ . '/vendor/autoload.php' );
WP_Dependency_Installer::instance()->run( __DIR__ );

/**
 * Returns the main instance of LEAN_WP to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object LEAN_WP
 */
function LEAN_WP () {
	$instance = LEAN_WP::instance( __FILE__, '1.0.0' );

/*
	if ( is_null( $instance->settings ) ) {
		$instance->settings = LEAN_WP_Settings::instance( $instance );
	}
*/

}

LEAN_WP();
