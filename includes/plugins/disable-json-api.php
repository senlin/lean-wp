<?php
/**
 * Plugin Name: Disable REST API
 * Plugin URI: http://www.binarytemplar.com/disable-json-api
 * Description: Disable the use of the JSON REST API on your website to anonymous users
 * Version: 1.3
 * Author: Dave McHale
 * Author URI: http://www.binarytemplar.com
 * License: GPL2+
 */

/**
 * ADDED BY COMPANYWP
 *
 */
 
add_action( 'after_setup_theme', 'cwp_remove_api' );

function cwp_remove_api () {
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
}
 

$dra_current_WP_version = get_bloginfo('version');

if ( version_compare( $dra_current_WP_version, '4.7', '>=' ) ) {
    DRA_Force_Auth_Error();
} else {
    DRA_Disable_Via_Filters();
}

//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/
//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/
//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/

/**
 * This function is called if the current version of WordPress is 4.7 or above
 * Forcibly raise an authentication error to the REST API if the user is not logged in
 */
function DRA_Force_Auth_Error() {
    add_filter( 'rest_authentication_errors', 'DRA_only_allow_logged_in_rest_access' );
}

/**
 * This function gets called if the current version of WordPress is less than 4.7
 * We are able to make use of filters to actually disable the functionality entirely
 */
function DRA_Disable_Via_Filters() {
    
	// Filters for WP-API version 1.x
    add_filter( 'json_enabled', '__return_false' );
    add_filter( 'json_jsonp_enabled', '__return_false' );

    // Filters for WP-API version 2.x
    add_filter( 'rest_enabled', '__return_false' );
    add_filter( 'rest_jsonp_enabled', '__return_false' );

    // Remove REST API info from head and headers
    remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
    remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
    remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	
}

/**
 * Returning an authentication error if a user who is not logged in tries to query the REST API
 * @param $access
 * @return WP_Error
 */
function DRA_only_allow_logged_in_rest_access( $access ) {

	if( ! is_user_logged_in() ) {
        return new WP_Error( 'rest_cannot_access', __( 'Only authenticated users can access the REST API.', 'disable-json-api' ), array( 'status' => rest_authorization_required_code() ) );
    }

    return $access;
	
}